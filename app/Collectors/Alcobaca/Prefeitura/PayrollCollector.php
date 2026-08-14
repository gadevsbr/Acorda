<?php

namespace App\Collectors\Alcobaca\Prefeitura;

use App\Collectors\Support\CanonicalJson;
use App\Collectors\Support\SourceAlertManager;
use App\Models\CollectorCheckpoint;
use App\Models\CollectorRun;
use App\Models\RawSourceRecord;
use App\Models\Source;
use App\Models\SourceHealthCheck;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

final class PayrollCollector
{
    public const NAME = 'alcobaca.prefeitura.payroll';

    public const VERSION = '1.0.0';

    public function __construct(
        private readonly PrefeituraApiClient $client,
        private readonly PayrollEnvelopeValidator $validator,
        private readonly SourceAlertManager $alertManager,
    ) {}

    /** @param array<string, int|string> $filters */
    public function collect(array $filters = [], ?int $maxPages = null, ?int $perPage = null): PayrollCollectorResult
    {
        $source = PrefeituraSource::resolve();
        $this->assertEnabled($source);

        $filters = array_intersect_key($filters, array_flip(['ano_referencia', 'mes_referencia']));
        $maxPages ??= (int) config('collectors.payroll_max_pages');
        $perPage ??= (int) config('collectors.payroll_per_page');

        if ($maxPages < 1) {
            throw new \InvalidArgumentException('maxPages deve ser maior ou igual a 1.');
        }

        $checkpoint = CollectorCheckpoint::query()->firstOrCreate(
            ['source_id' => $source->id, 'collector' => self::NAME, 'key' => 'pagination'],
            ['value' => ['next_page' => 1, 'in_progress' => false]],
        );
        $filterHash = hash('sha256', CanonicalJson::encode($filters));
        $checkpointValue = (array) $checkpoint->value;
        $page = ($checkpointValue['in_progress'] ?? false) && ($checkpointValue['filter_hash'] ?? null) === $filterHash
            ? max(1, (int) ($checkpointValue['next_page'] ?? 1))
            : 1;

        $run = CollectorRun::query()->create([
            'source_id' => $source->id,
            'collector' => self::NAME,
            'collector_version' => self::VERSION,
            'status' => 'running',
            'started_at' => now(),
            'checkpoint_before' => $checkpointValue,
        ]);

        $counters = ['fetched' => 0, 'created' => 0, 'unchanged' => 0, 'invalid' => 0];
        $pagesProcessed = 0;
        $lastPage = 0;
        $lastResponse = null;

        try {
            do {
                $payrollPage = $this->client->payrollPage($page, $perPage, $filters);
                $lastResponse = $payrollPage;
                $lastPage = $payrollPage->lastPage;
                $pageCounters = $this->storePage($source, $run, $payrollPage);

                foreach ($counters as $key => $value) {
                    $counters[$key] = $value + $pageCounters[$key];
                }

                $page++;
                $pagesProcessed++;
                $inProgress = $lastPage > 0 && $page <= $lastPage;
                $checkpoint->update(['value' => [
                    'next_page' => $inProgress ? $page : 1,
                    'in_progress' => $inProgress,
                    'filter_hash' => $filterHash,
                    'filters' => $filters,
                    'last_completed_page' => $page - 1,
                    'last_completed_at' => now()->toIso8601String(),
                ]]);
            } while ($page <= $lastPage && $pagesProcessed < $maxPages);

            $hasMorePages = $lastPage > 0 && $page <= $lastPage;
            $status = $hasMorePages || ($lastResponse?->total ?? 0) === 0 ? 'partial' : 'success';
            $message = match (true) {
                $hasMorePages => 'Limite de páginas atingido; continuação preservada no checkpoint.',
                ($lastResponse?->total ?? 0) === 0 => 'Resposta válida, porém sem registros. Ausência na fonte não confirma ausência do fato.',
                default => 'Coleta concluída com schema válido.',
            };

            $run->update([
                'status' => $status,
                'finished_at' => now(),
                'checkpoint_after' => $checkpoint->fresh()->value,
                'records_fetched' => $counters['fetched'],
                'records_created' => $counters['created'],
                'records_unchanged' => $counters['unchanged'],
                'records_invalid' => $counters['invalid'],
                'http_status' => $lastResponse?->httpStatus,
            ]);

            $source->update([
                'status' => $status === 'success' ? 'operational' : 'partial',
                'last_successful_at' => now(),
            ]);
            $this->recordHealth($source, $lastResponse, $status === 'success' ? 'operational' : 'partial', $message);

            return new PayrollCollectorResult(
                runId: $run->id,
                status: $status,
                fetched: $counters['fetched'],
                created: $counters['created'],
                unchanged: $counters['unchanged'],
                invalid: $counters['invalid'],
                nextPage: $hasMorePages ? $page : 1,
            );
        } catch (Throwable $exception) {
            $httpStatus = $exception instanceof RequestException ? $exception->response->status() : null;
            $healthStatus = $exception instanceof ValidationException ? 'schema_changed' : 'unavailable';

            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'checkpoint_after' => $checkpoint->fresh()->value,
                'records_fetched' => $counters['fetched'],
                'records_created' => $counters['created'],
                'records_unchanged' => $counters['unchanged'],
                'records_invalid' => $counters['invalid'],
                'http_status' => $httpStatus,
                'error_class' => $exception::class,
                'error_message' => mb_substr($exception->getMessage(), 0, 4000),
            ]);
            $source->update(['status' => $healthStatus]);
            $this->recordHealth($source, null, $healthStatus, $exception->getMessage(), $httpStatus);

            throw $exception;
        }
    }

    /**
     * @return array{fetched: int, created: int, unchanged: int, invalid: int}
     */
    private function storePage(Source $source, CollectorRun $run, PayrollPage $page): array
    {
        return DB::transaction(function () use ($source, $run, $page): array {
            $counters = ['fetched' => 0, 'created' => 0, 'unchanged' => 0, 'invalid' => 0];

            foreach ($page->records as $record) {
                $canonicalPayload = CanonicalJson::encode($record);
                $checksum = hash('sha256', $canonicalPayload);
                $errors = $this->validator->recordErrors($record);
                $externalId = array_key_exists('id', $record)
                    ? (string) $record['id']
                    : 'invalid:'.$checksum;

                $rawRecord = RawSourceRecord::query()->firstOrCreate(
                    [
                        'source_id' => $source->id,
                        'external_id' => $externalId,
                        'checksum' => $checksum,
                    ],
                    [
                        'collector_run_id' => $run->id,
                        'source_url' => $page->url,
                        'fetched_at' => now(),
                        'source_updated_at' => $this->parseSourceDate($record['updated_at'] ?? null),
                        'payload' => $record,
                        'content_type' => $page->contentType,
                        'etag' => $page->etag,
                        'last_modified' => $page->lastModified,
                        'http_status' => $page->httpStatus,
                        'collector' => self::NAME,
                        'collector_version' => self::VERSION,
                        'validation_status' => $errors === [] ? 'valid' : 'invalid',
                        'validation_errors' => $errors === [] ? null : $errors,
                    ],
                );

                $counters['fetched']++;
                $counters[$rawRecord->wasRecentlyCreated ? 'created' : 'unchanged']++;
                if ($errors !== []) {
                    $counters['invalid']++;
                }
            }

            return $counters;
        });
    }

    private function recordHealth(
        Source $source,
        ?PayrollPage $page,
        string $status,
        string $message,
        ?int $httpStatus = null,
    ): void {
        $schemaKeys = $page?->records[0] ?? $page?->envelope ?? [];

        $health = SourceHealthCheck::query()->create([
            'source_id' => $source->id,
            'checked_at' => now(),
            'status' => $status,
            'http_status' => $httpStatus ?? $page?->httpStatus,
            'response_time_ms' => $page?->responseTimeMs,
            'records_count' => $page?->total,
            'schema_checksum' => $schemaKeys === [] ? null : hash('sha256', CanonicalJson::encode(array_keys($schemaKeys))),
            'message' => mb_substr($message, 0, 4000),
        ]);

        $this->alertManager->evaluate($source, $health);
    }

    private function parseSourceDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function assertEnabled(Source $source): void
    {
        if (! $source->enabled) {
            throw new \RuntimeException('A fonte está desabilitada e não pode ser coletada.');
        }
    }
}
