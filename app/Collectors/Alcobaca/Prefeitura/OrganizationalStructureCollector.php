<?php

namespace App\Collectors\Alcobaca\Prefeitura;

use App\Collectors\Support\CanonicalJson;
use App\Collectors\Support\SourceAlertManager;
use App\Models\CollectorCheckpoint;
use App\Models\CollectorRun;
use App\Models\Organization;
use App\Models\RawSourceRecord;
use App\Models\Source;
use App\Models\SourceHealthCheck;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class OrganizationalStructureCollector
{
    public const NAME = 'alcobaca.prefeitura.organizational-structure';

    public const VERSION = '1.0.0';

    public function __construct(
        private readonly PrefeituraApiClient $client,
        private readonly OrganizationalStructureValidator $validator,
        private readonly SourceAlertManager $alertManager,
    ) {}

    public function collect(int $maxPages = 10, int $perPage = 100): OrganizationalStructureCollectorResult
    {
        if ($maxPages < 1 || $perPage < 1 || $perPage > 500) {
            throw new \InvalidArgumentException('Limites de paginação inválidos.');
        }

        $source = OrganizationalStructureSource::resolve();
        if (! $source->enabled) {
            throw new \RuntimeException('A fonte está desabilitada e não pode ser coletada.');
        }

        $checkpoint = CollectorCheckpoint::query()->firstOrCreate(
            ['source_id' => $source->id, 'collector' => self::NAME, 'key' => 'pagination'],
            ['value' => ['next_page' => 1, 'in_progress' => false]],
        );
        $checkpointValue = (array) $checkpoint->value;
        $pageNumber = ($checkpointValue['in_progress'] ?? false)
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
        $counters = ['fetched' => 0, 'created' => 0, 'unchanged' => 0, 'invalid' => 0, 'normalized' => 0];
        $pagesProcessed = 0;
        $lastPage = 0;
        $lastResponse = null;

        try {
            do {
                $page = $this->client->organizationalStructurePage($pageNumber, $perPage);
                $lastResponse = $page;
                $lastPage = $page->lastPage;
                $pageCounters = $this->storePage($source, $run, $page);
                foreach ($counters as $key => $value) {
                    $counters[$key] = $value + $pageCounters[$key];
                }

                $pageNumber++;
                $pagesProcessed++;
                $inProgress = $lastPage > 0 && $pageNumber <= $lastPage;
                $checkpoint->update(['value' => [
                    'next_page' => $inProgress ? $pageNumber : 1,
                    'in_progress' => $inProgress,
                    'last_completed_page' => $pageNumber - 1,
                    'last_completed_at' => now()->toIso8601String(),
                ]]);
            } while ($pageNumber <= $lastPage && $pagesProcessed < $maxPages);

            $this->resolveExactParents($source);
            $hasMorePages = $lastPage > 0 && $pageNumber <= $lastPage;
            $isEmpty = ($lastResponse?->total ?? 0) === 0;
            $status = $hasMorePages || $isEmpty || $counters['invalid'] > 0 ? 'partial' : 'success';
            $message = match (true) {
                $hasMorePages => 'Limite de páginas atingido; continuação preservada no checkpoint.',
                $isEmpty => 'Resposta válida sem organizações; ausência não confirma inexistência.',
                $counters['invalid'] > 0 => 'Parte dos registros não passou pela validação e não foi normalizada.',
                default => 'Estrutura organizacional coletada e normalizada com schema válido.',
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

            return new OrganizationalStructureCollectorResult(
                runId: $run->id,
                status: $status,
                fetched: $counters['fetched'],
                created: $counters['created'],
                unchanged: $counters['unchanged'],
                invalid: $counters['invalid'],
                normalized: $counters['normalized'],
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

    /** @return array{fetched: int, created: int, unchanged: int, invalid: int, normalized: int} */
    private function storePage(Source $source, CollectorRun $run, OrganizationalStructurePage $page): array
    {
        return DB::transaction(function () use ($source, $run, $page): array {
            $counters = ['fetched' => 0, 'created' => 0, 'unchanged' => 0, 'invalid' => 0, 'normalized' => 0];

            foreach ($page->records as $record) {
                $canonicalPayload = CanonicalJson::encode($record);
                $checksum = hash('sha256', $canonicalPayload);
                $errors = $this->validator->recordErrors($record);
                $externalId = array_key_exists('id', $record) ? (string) $record['id'] : 'invalid:'.$checksum;
                $rawRecord = RawSourceRecord::query()->firstOrCreate(
                    ['source_id' => $source->id, 'external_id' => $externalId, 'checksum' => $checksum],
                    [
                        'collector_run_id' => $run->id,
                        'source_url' => $page->url,
                        'fetched_at' => now(),
                        'source_updated_at' => $this->parseDate($record['updated_at'] ?? null),
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

                    continue;
                }

                $this->normalize($source, $rawRecord, $record);
                $counters['normalized']++;
            }

            return $counters;
        });
    }

    /** @param array<string, mixed> $record */
    private function normalize(Source $source, RawSourceRecord $rawRecord, array $record): void
    {
        $name = trim((string) $record['nome']);
        $externalId = (string) $record['id'];
        Organization::query()->updateOrCreate(
            ['source_id' => $source->id, 'external_id' => $externalId],
            [
                'source_record_id' => $rawRecord->id,
                'municipality_ibge_code' => '2900801',
                'public_slug' => Str::slug($name).'-'.Str::slug($externalId),
                'name' => $name,
                'normalized_name' => $this->normalizeName($name),
                'type' => $this->inferType($name),
                'responsible_name' => $this->nullableString($record['responsavel'] ?? null),
                'parent_source_name' => $this->nullableString($record['orgao_vinculado_nome'] ?? null),
                'phone' => $this->nullableString($record['telefone'] ?? null),
                'email' => $this->nullableString($record['email'] ?? null),
                'address' => $this->nullableString($record['endereco'] ?? null),
                'competencies' => $this->nullableString($record['competencias'] ?? null),
                'opening_hours' => $this->nullableString($record['funcionamento'] ?? null),
                'source_updated_at' => $this->parseDate($record['updated_at'] ?? null),
                'is_current' => true,
            ],
        );
    }

    private function resolveExactParents(Source $source): void
    {
        $organizations = Organization::query()->where('source_id', $source->id)->get();
        $byName = $organizations->groupBy('normalized_name');

        foreach ($organizations as $organization) {
            $parentName = $organization->parent_source_name;
            if ($parentName === null) {
                $organization->update(['parent_id' => null]);

                continue;
            }

            $matches = $byName->get($this->normalizeName($parentName), collect());
            $organization->update(['parent_id' => $matches->count() === 1 ? $matches->first()->id : null]);
        }
    }

    private function normalizeName(string $value): string
    {
        return Str::of($value)->ascii()->upper()->squish()->toString();
    }

    private function inferType(string $name): string
    {
        $normalized = $this->normalizeName($name);

        return match (true) {
            str_starts_with($normalized, 'SECRETARIA') => 'secretariat',
            str_starts_with($normalized, 'DEPARTAMENTO') => 'department',
            default => 'organization',
        };
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function parseDate(mixed $value): ?CarbonImmutable
    {
        try {
            return is_string($value) && $value !== '' ? CarbonImmutable::parse($value) : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function recordHealth(
        Source $source,
        ?OrganizationalStructurePage $page,
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
}
