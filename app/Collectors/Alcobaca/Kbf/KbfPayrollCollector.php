<?php

namespace App\Collectors\Alcobaca\Kbf;

use App\Collectors\Support\CanonicalJson;
use App\Collectors\Support\SourceAlertManager;
use App\Models\CollectorRun;
use App\Models\Employment;
use App\Models\PayrollRecord;
use App\Models\RawSourceRecord;
use App\Models\Source;
use App\Models\SourceHealthCheck;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

final class KbfPayrollCollector
{
    public const NAME = 'alcobaca.prefeitura.kbf-payroll';

    public const VERSION = '1.0.0';

    public function __construct(private readonly KbfClient $client, private readonly SourceAlertManager $alertManager) {}

    public function collect(int $month, int $year): KbfPayrollCollectorResult
    {
        $source = KbfPayrollSource::resolve();
        $run = CollectorRun::query()->create([
            'source_id' => $source->id, 'collector' => self::NAME, 'collector_version' => self::VERSION,
            'status' => 'running', 'started_at' => now(), 'checkpoint_before' => ['month' => $month, 'year' => $year],
        ]);
        $counters = ['fetched' => 0, 'created' => 0, 'unchanged' => 0, 'invalid' => 0, 'normalized' => 0];

        try {
            $dataset = $this->client->payroll($month, $year);
            $counters = $this->store($source, $run, $dataset);
            $status = $dataset->total === 0 || $counters['invalid'] > 0 ? 'partial' : 'success';
            $healthStatus = $status === 'success' ? 'operational' : 'partial';
            $run->update([
                'status' => $status, 'finished_at' => now(), 'checkpoint_after' => ['month' => $month, 'year' => $year],
                'records_fetched' => $counters['fetched'], 'records_created' => $counters['created'],
                'records_unchanged' => $counters['unchanged'], 'records_invalid' => $counters['invalid'],
                'http_status' => $dataset->httpStatus,
            ]);
            $source->update(['status' => $healthStatus, 'last_successful_at' => now()]);
            $message = $dataset->total === 0 ? 'Folha mensal KBF vazia; ausência não confirma inexistência.'
                : ($counters['invalid'] > 0 ? 'Parte da folha mensal falhou na validação e não foi normalizada.' : 'Folha mensal KBF preservada e normalizada com schema válido.');
            $this->health($source, $dataset, $healthStatus, $message);

            return new KbfPayrollCollectorResult($run->id, $status, ...array_values($counters));
        } catch (Throwable $exception) {
            $run->update(['status' => 'failed', 'finished_at' => now(), 'error_class' => $exception::class, 'error_message' => mb_substr($exception->getMessage(), 0, 4000)]);
            $source->update(['status' => 'unavailable']);
            $health = SourceHealthCheck::query()->create(['source_id' => $source->id, 'checked_at' => now(), 'status' => 'unavailable', 'message' => mb_substr($exception->getMessage(), 0, 4000)]);
            $this->alertManager->evaluate($source, $health);
            throw $exception;
        }
    }

    /** @return array{fetched:int,created:int,unchanged:int,invalid:int,normalized:int} */
    private function store(Source $source, CollectorRun $run, KbfPayrollDataset $dataset): array
    {
        return DB::transaction(function () use ($source, $run, $dataset): array {
            $counters = ['fetched' => 0, 'created' => 0, 'unchanged' => 0, 'invalid' => 0, 'normalized' => 0];
            foreach ($dataset->records as $record) {
                $errors = $this->errors($record, $dataset);
                $checksum = hash('sha256', CanonicalJson::encode($record));
                $registration = is_string($record['registration'] ?? null) ? $record['registration'] : 'invalid';
                $type = is_string($record['calculation_type'] ?? null) ? $record['calculation_type'] : 'invalid';
                $externalId = sprintf('%04d-%02d:%s:%s', $dataset->year, $dataset->month, $registration, hash('sha256', $type));
                $raw = RawSourceRecord::query()->firstOrCreate(
                    ['source_id' => $source->id, 'external_id' => $externalId, 'checksum' => $checksum],
                    ['collector_run_id' => $run->id, 'source_url' => $dataset->url, 'fetched_at' => now(),
                        'payload' => $record, 'content_type' => $dataset->contentType, 'http_status' => $dataset->httpStatus,
                        'collector' => self::NAME, 'collector_version' => self::VERSION,
                        'validation_status' => $errors === [] ? 'valid' : 'invalid', 'validation_errors' => $errors ?: null],
                );
                $counters['fetched']++;
                $counters[$raw->wasRecentlyCreated ? 'created' : 'unchanged']++;
                if ($errors !== []) {
                    $counters['invalid']++;

                    continue;
                }
                $this->normalize($source, $raw, $record, $dataset);
                $counters['normalized']++;
            }

            return $counters;
        });
    }

    /** @param array<string, int|string|null> $record @return array<int, string> */
    private function errors(array $record, KbfPayrollDataset $dataset): array
    {
        $errors = [];
        foreach (['registration', 'name', 'reference', 'calculation_type'] as $field) {
            if (! is_string($record[$field] ?? null) || trim((string) $record[$field]) === '') {
                $errors[] = "{$field} é obrigatório.";
            }
        }
        foreach (['gross_cents', 'deductions_cents', 'net_cents'] as $field) {
            if (! is_int($record[$field] ?? null)) {
                $errors[] = "{$field} deve ser inteiro.";
            }
        }
        if (is_int($record['gross_cents'] ?? null) && is_int($record['deductions_cents'] ?? null) && is_int($record['net_cents'] ?? null)
            && $record['net_cents'] !== $record['gross_cents'] - $record['deductions_cents']) {
            $errors[] = 'Vencimento menos desconto deve ser igual ao líquido.';
        }
        $expected = $this->monthName($dataset->month).'/'.$dataset->year;
        if (($record['reference'] ?? null) !== $expected) {
            $errors[] = 'Competência do registro diverge da solicitada.';
        }
        foreach (['admission_date', 'termination_date'] as $field) {
            if (($record[$field] ?? null) !== null && $this->date((string) $record[$field]) === null) {
                $errors[] = "{$field} é inválida.";
            }
        }

        return $errors;
    }

    /** @param array<string, int|string|null> $record */
    private function normalize(Source $source, RawSourceRecord $raw, array $record, KbfPayrollDataset $dataset): void
    {
        $registration = (string) $record['registration'];
        $type = (string) $record['calculation_type'];
        $previous = PayrollRecord::query()->where([
            'source_id' => $source->id, 'registration' => $registration,
            'reference_year' => $dataset->year, 'reference_month' => $dataset->month,
            'calculation_type' => $type, 'is_latest' => true,
        ])->first();
        $payroll = PayrollRecord::query()->firstOrCreate(['source_record_id' => $raw->id], [
            'source_id' => $source->id,
            'employment_id' => Employment::query()->where('registration', $registration)->value('id'),
            'supersedes_id' => $previous?->id,
            'registration' => $registration, 'employee_name' => (string) $record['name'],
            'reference_year' => $dataset->year, 'reference_month' => $dataset->month, 'calculation_type' => $type,
            'gross_cents' => $record['gross_cents'], 'deductions_cents' => $record['deductions_cents'], 'net_cents' => $record['net_cents'],
            'position_name' => $record['position'], 'weekly_workload' => $record['weekly_workload'],
            'cost_center' => $record['cost_center'], 'workplace' => $record['workplace'],
            'admission_date' => $this->date(is_string($record['admission_date']) ? $record['admission_date'] : null),
            'termination_date' => $this->date(is_string($record['termination_date']) ? $record['termination_date'] : null),
            'is_latest' => true,
        ]);
        if ($payroll->wasRecentlyCreated && $previous !== null && $previous->id !== $payroll->id) {
            $previous->update(['is_latest' => false]);
        }
    }

    private function date(?string $value): ?CarbonImmutable
    {
        try {
            return $value ? CarbonImmutable::createFromFormat('d/m/Y', $value)->startOfDay() : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function monthName(int $month): string
    {
        return [1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'][$month];
    }

    private function health(Source $source, KbfPayrollDataset $dataset, string $status, string $message): void
    {
        $health = SourceHealthCheck::query()->create([
            'source_id' => $source->id, 'checked_at' => now(), 'status' => $status, 'http_status' => $dataset->httpStatus,
            'response_time_ms' => $dataset->responseTimeMs, 'records_count' => $dataset->total,
            'schema_checksum' => hash('sha256', CanonicalJson::encode(array_keys($dataset->records[0] ?? []))), 'message' => $message,
        ]);
        $this->alertManager->evaluate($source, $health);
    }
}
