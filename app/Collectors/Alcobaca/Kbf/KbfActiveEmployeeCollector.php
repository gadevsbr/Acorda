<?php

namespace App\Collectors\Alcobaca\Kbf;

use App\Collectors\Support\CanonicalJson;
use App\Collectors\Support\SourceAlertManager;
use App\Models\CollectorRun;
use App\Models\RawSourceRecord;
use App\Models\Source;
use App\Models\SourceHealthCheck;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

final class KbfActiveEmployeeCollector
{
    public const NAME = 'alcobaca.prefeitura.kbf-active-employees';

    public const VERSION = '1.0.0';

    public function __construct(
        private readonly KbfClient $client,
        private readonly SourceAlertManager $alertManager,
    ) {}

    public function collect(): KbfActiveEmployeeCollectorResult
    {
        $source = KbfSource::resolve();
        $run = CollectorRun::query()->create([
            'source_id' => $source->id, 'collector' => self::NAME, 'collector_version' => self::VERSION,
            'status' => 'running', 'started_at' => now(),
        ]);
        $counters = ['fetched' => 0, 'created' => 0, 'unchanged' => 0, 'invalid' => 0];

        try {
            $dataset = $this->client->activeEmployees();
            $counters = $this->store($source, $run, $dataset);
            $status = $dataset->total === 0 || $counters['invalid'] > 0 ? 'partial' : 'success';
            $healthStatus = $status === 'success' ? 'operational' : 'partial';
            $message = $dataset->total === 0
                ? 'A grade KBF respondeu sem servidores; ausência não confirma inexistência.'
                : ($counters['invalid'] > 0 ? 'Parte dos vínculos KBF permaneceu bruta por falha de validação.' : 'Grade de servidores ativos KBF preservada com schema válido.');
            $run->update([
                'status' => $status, 'finished_at' => now(), 'records_fetched' => $counters['fetched'],
                'records_created' => $counters['created'], 'records_unchanged' => $counters['unchanged'],
                'records_invalid' => $counters['invalid'], 'http_status' => $dataset->httpStatus,
            ]);
            $source->update(['status' => $healthStatus, 'last_successful_at' => now()]);
            $this->health($source, $dataset, $healthStatus, $message);

            return new KbfActiveEmployeeCollectorResult($run->id, $status, ...array_values($counters));
        } catch (Throwable $exception) {
            $run->update(['status' => 'failed', 'finished_at' => now(), 'error_class' => $exception::class, 'error_message' => mb_substr($exception->getMessage(), 0, 4000)]);
            $source->update(['status' => 'unavailable']);
            $health = SourceHealthCheck::query()->create(['source_id' => $source->id, 'checked_at' => now(), 'status' => 'unavailable', 'message' => mb_substr($exception->getMessage(), 0, 4000)]);
            $this->alertManager->evaluate($source, $health);
            throw $exception;
        }
    }

    /** @return array{fetched:int,created:int,unchanged:int,invalid:int} */
    private function store(Source $source, CollectorRun $run, KbfActiveEmployeeDataset $dataset): array
    {
        return DB::transaction(function () use ($source, $run, $dataset): array {
            $counters = ['fetched' => 0, 'created' => 0, 'unchanged' => 0, 'invalid' => 0];
            foreach ($dataset->records as $record) {
                $errors = $this->errors($record);
                $payload = CanonicalJson::encode($record);
                $checksum = hash('sha256', $payload);
                $externalId = $record['registration'] ?? 'invalid:'.$checksum;
                $raw = RawSourceRecord::query()->firstOrCreate(
                    ['source_id' => $source->id, 'external_id' => $externalId, 'checksum' => $checksum],
                    ['collector_run_id' => $run->id, 'source_url' => $dataset->url, 'fetched_at' => now(),
                        'source_updated_at' => $this->date($record['admission_date'] ?? null), 'payload' => $record,
                        'content_type' => $dataset->contentType, 'http_status' => $dataset->httpStatus,
                        'collector' => self::NAME, 'collector_version' => self::VERSION,
                        'validation_status' => $errors === [] ? 'valid' : 'invalid', 'validation_errors' => $errors ?: null],
                );
                $counters['fetched']++;
                $counters[$raw->wasRecentlyCreated ? 'created' : 'unchanged']++;
                if ($errors !== []) {
                    $counters['invalid']++;
                }
            }

            return $counters;
        });
    }

    /** @param array<string, string|null> $record @return array<int, string> */
    private function errors(array $record): array
    {
        $errors = [];
        foreach (['registration', 'name', 'employment_regime', 'position'] as $field) {
            if (! is_string($record[$field] ?? null) || trim((string) $record[$field]) === '') {
                $errors[] = "{$field} é obrigatório.";
            }
        }
        if (($record['admission_date'] ?? null) !== null && $this->date($record['admission_date']) === null) {
            $errors[] = 'admission_date é inválida.';
        }

        return $errors;
    }

    private function date(?string $value): ?CarbonImmutable
    {
        try {
            return $value ? CarbonImmutable::createFromFormat('d/m/Y', $value)->startOfDay() : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function health(Source $source, KbfActiveEmployeeDataset $dataset, string $status, string $message): void
    {
        $health = SourceHealthCheck::query()->create([
            'source_id' => $source->id, 'checked_at' => now(), 'status' => $status,
            'http_status' => $dataset->httpStatus, 'response_time_ms' => $dataset->responseTimeMs,
            'records_count' => $dataset->total,
            'schema_checksum' => hash('sha256', CanonicalJson::encode(array_keys($dataset->records[0] ?? []))),
            'message' => $message,
        ]);
        $this->alertManager->evaluate($source, $health);
    }
}
