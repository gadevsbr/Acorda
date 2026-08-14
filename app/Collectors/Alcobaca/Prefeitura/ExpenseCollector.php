<?php

namespace App\Collectors\Alcobaca\Prefeitura;

use App\Collectors\Support\CanonicalJson;
use App\Collectors\Support\SourceAlertManager;
use App\Models\CollectorCheckpoint;
use App\Models\CollectorRun;
use App\Models\RawSourceRecord;
use App\Models\SourceHealthCheck;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ExpenseCollector
{
    public const NAME = 'alcobaca.prefeitura.expenses';

    public const VERSION = '1.0.0';

    public function __construct(private readonly PrefeituraApiClient $client, private readonly ExpenseValidator $validator, private readonly SourceAlertManager $alerts) {}

    public function collect(int $maxPages = 20, int $perPage = 100): ExpenseCollectorResult
    {
        if ($maxPages < 1 || $perPage < 1 || $perPage > 500) {
            throw new \InvalidArgumentException('Limites de paginação inválidos.');
        }
        $source = ExpenseSource::resolve();
        $checkpoint = CollectorCheckpoint::query()->firstOrCreate(
            ['source_id' => $source->id, 'collector' => self::NAME, 'key' => 'pagination'],
            ['value' => ['next_page' => 1, 'in_progress' => false]],
        );
        $before = (array) $checkpoint->value;
        $pageNumber = ($before['in_progress'] ?? false) ? max(1, (int) ($before['next_page'] ?? 1)) : 1;
        $run = CollectorRun::query()->create(['source_id' => $source->id, 'collector' => self::NAME, 'collector_version' => self::VERSION, 'status' => 'running', 'started_at' => now(), 'checkpoint_before' => $before]);
        $counts = ['fetched' => 0, 'created' => 0, 'unchanged' => 0, 'invalid' => 0];
        $last = null;
        try {
            for ($processed = 0; $processed < $maxPages; $processed++) {
                $last = $this->client->expensePage($pageNumber, $perPage);
                foreach ($this->store($run, $last) as $key => $value) {
                    $counts[$key] += $value;
                }
                $pageNumber++;
                $more = $last->lastPage > 0 && $pageNumber <= $last->lastPage;
                $checkpoint->update(['value' => ['next_page' => $more ? $pageNumber : 1, 'in_progress' => $more, 'last_completed_page' => $pageNumber - 1, 'last_completed_at' => now()->toIso8601String()]]);
                if (! $more) {
                    break;
                }
            }
            $hasMore = (bool) ($checkpoint->fresh()->value['in_progress'] ?? false);
            $empty = ($last?->total ?? 0) === 0;
            $status = $hasMore || $empty || $counts['invalid'] > 0 ? 'partial' : 'success';
            $message = $empty ? 'Resposta válida sem despesas; ausência não confirma inexistência.' : ($hasMore ? 'Limite de páginas atingido; continuação preservada.' : ($counts['invalid'] ? 'Há registros brutos inválidos não publicados.' : 'Despesas brutas preservadas com schema válido.'));
            $run->update(['status' => $status, 'finished_at' => now(), 'checkpoint_after' => $checkpoint->fresh()->value, 'records_fetched' => $counts['fetched'], 'records_created' => $counts['created'], 'records_unchanged' => $counts['unchanged'], 'records_invalid' => $counts['invalid'], 'http_status' => $last?->httpStatus]);
            $source->update(['status' => $status === 'success' ? 'operational' : 'partial', 'last_successful_at' => now()]);
            $health = SourceHealthCheck::query()->create(['source_id' => $source->id, 'checked_at' => now(), 'status' => $status === 'success' ? 'operational' : 'partial', 'http_status' => $last?->httpStatus, 'response_time_ms' => $last?->responseTimeMs, 'records_count' => $last?->total, 'message' => $message]);
            $this->alerts->evaluate($source, $health);

            return new ExpenseCollectorResult($run->id, $status, ...array_values($counts));
        } catch (Throwable $exception) {
            $run->update(['status' => 'failed', 'finished_at' => now(), 'error_class' => $exception::class, 'error_message' => mb_substr($exception->getMessage(), 0, 4000)]);
            $source->update(['status' => 'unavailable']);
            throw $exception;
        }
    }

    private function store(CollectorRun $run, ExpensePage $page): array
    {
        return DB::transaction(function () use ($run, $page): array {
            $counts = ['fetched' => 0, 'created' => 0, 'unchanged' => 0, 'invalid' => 0];
            foreach ($page->records as $record) {
                $checksum = hash('sha256', CanonicalJson::encode($record));
                $errors = $this->validator->recordErrors($record);
                $raw = RawSourceRecord::query()->firstOrCreate(
                    ['source_id' => $run->source_id, 'external_id' => isset($record['des_codigo']) ? (string) $record['des_codigo'] : 'invalid:'.$checksum, 'checksum' => $checksum],
                    ['collector_run_id' => $run->id, 'source_url' => $page->url, 'fetched_at' => now(), 'payload' => $record, 'content_type' => $page->contentType, 'etag' => $page->etag, 'last_modified' => $page->lastModified, 'http_status' => $page->httpStatus, 'collector' => self::NAME, 'collector_version' => self::VERSION, 'validation_status' => $errors ? 'invalid' : 'valid', 'validation_errors' => $errors ?: null],
                );
                $counts['fetched']++;
                $counts[$raw->wasRecentlyCreated ? 'created' : 'unchanged']++;
                if ($errors) {
                    $counts['invalid']++;
                }
            }

            return $counts;
        });
    }
}
