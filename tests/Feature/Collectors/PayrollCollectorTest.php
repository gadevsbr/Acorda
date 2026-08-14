<?php

namespace Tests\Feature\Collectors;

use App\Collectors\Alcobaca\Prefeitura\PayrollCollector;
use App\Models\CollectorCheckpoint;
use App\Models\RawSourceRecord;
use App\Models\SourceHealthCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PayrollCollectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_collects_all_pages_and_preserves_raw_provenance(): void
    {
        $this->fakePaginatedPayroll();

        $result = app(PayrollCollector::class)->collect([
            'ano_referencia' => 2026,
            'mes_referencia' => 7,
        ], maxPages: 5, perPage: 2);

        $this->assertSame('success', $result->status);
        $this->assertSame(3, $result->fetched);
        $this->assertSame(3, $result->created);
        $this->assertSame(0, $result->invalid);
        $this->assertDatabaseCount('raw_source_records', 3);
        $this->assertDatabaseHas('raw_source_records', [
            'external_id' => 'fixture-001',
            'validation_status' => 'valid',
            'http_status' => 200,
            'collector' => PayrollCollector::NAME,
        ]);
        $this->assertDatabaseHas('collector_runs', [
            'id' => $result->runId,
            'status' => 'success',
            'records_created' => 3,
        ]);
        $this->assertDatabaseHas('source_health_checks', [
            'status' => 'operational',
            'records_count' => 3,
        ]);

        $checkpoint = CollectorCheckpoint::query()->sole();
        $this->assertFalse($checkpoint->value['in_progress']);
        $this->assertSame(1, $checkpoint->value['next_page']);

        $record = RawSourceRecord::query()->where('external_id', 'fixture-001')->sole();
        $this->assertNotEmpty($record->checksum);
        $this->assertStringContainsString('ano_referencia=2026', $record->source_url);
        $this->assertSame('SERVIDOR EXEMPLO UM', $record->payload['funcionario']);
    }

    public function test_reprocessing_the_same_payload_is_idempotent(): void
    {
        $this->fakePaginatedPayroll();
        $collector = app(PayrollCollector::class);

        $collector->collect(maxPages: 5, perPage: 2);
        $result = $collector->collect(maxPages: 5, perPage: 2);

        $this->assertSame(0, $result->created);
        $this->assertSame(3, $result->unchanged);
        $this->assertDatabaseCount('raw_source_records', 3);
        $this->assertDatabaseCount('collector_runs', 2);
    }

    public function test_it_resumes_from_the_checkpoint_after_a_page_limit(): void
    {
        $this->fakePaginatedPayroll();
        $collector = app(PayrollCollector::class);

        $firstRun = $collector->collect(maxPages: 1, perPage: 2);
        $this->assertSame('partial', $firstRun->status);
        $this->assertSame(2, $firstRun->nextPage);
        $this->assertDatabaseCount('raw_source_records', 2);

        $secondRun = $collector->collect(maxPages: 1, perPage: 2);
        $this->assertSame('success', $secondRun->status);
        $this->assertSame(1, $secondRun->nextPage);
        $this->assertSame(1, $secondRun->fetched);
        $this->assertDatabaseCount('raw_source_records', 3);
    }

    public function test_a_changed_payload_creates_a_new_auditable_version(): void
    {
        $changed = false;
        $pageOne = $this->fixture('payroll-page-1.json');
        $pageTwo = $this->fixture('payroll-page-2.json');
        Http::fake(function (Request $request) use (&$changed, $pageOne, $pageTwo) {
            if ((int) $request['page'] === 2) {
                return Http::response($pageTwo, 200, ['Content-Type' => 'application/json']);
            }

            if ($changed) {
                $pageOne['data'][0]['salario_bruto'] = 5100;
            }

            return Http::response($pageOne, 200, ['Content-Type' => 'application/json']);
        });
        $collector = app(PayrollCollector::class);
        $collector->collect(maxPages: 5, perPage: 2);

        $changed = true;
        $result = $collector->collect(maxPages: 5, perPage: 2);

        $this->assertSame(1, $result->created);
        $this->assertDatabaseCount('raw_source_records', 4);
        $this->assertSame(
            2,
            RawSourceRecord::query()->where('external_id', 'fixture-001')->count(),
        );
    }

    public function test_an_empty_but_valid_source_is_marked_partial_not_absent(): void
    {
        Http::fake([
            '*' => Http::response([
                'total' => 0,
                'per_page' => 50,
                'current_page' => 1,
                'last_page' => 0,
                'data' => [],
            ], 200, ['Content-Type' => 'application/json']),
        ]);

        $result = app(PayrollCollector::class)->collect(maxPages: 1, perPage: 50);

        $this->assertSame('partial', $result->status);
        $this->assertDatabaseHas('sources', ['status' => 'partial']);
        $health = SourceHealthCheck::query()->sole();
        $this->assertSame('partial', $health->status);
        $this->assertStringContainsString('não confirma ausência', $health->message);
    }

    public function test_a_schema_change_fails_closed_and_records_the_failure(): void
    {
        Http::fake(['*' => Http::response(['items' => []], 200)]);

        try {
            app(PayrollCollector::class)->collect(maxPages: 1, perPage: 50);
            $this->fail('A coleta deveria falhar com schema inválido.');
        } catch (ValidationException) {
            $this->assertDatabaseHas('collector_runs', ['status' => 'failed']);
            $this->assertDatabaseHas('source_health_checks', ['status' => 'schema_changed']);
            $this->assertDatabaseHas('sources', ['status' => 'schema_changed']);
            $this->assertDatabaseCount('raw_source_records', 0);
        }
    }

    public function test_the_console_command_validates_options_and_records_a_partial_empty_source(): void
    {
        Http::fake(['*' => Http::response([
            'total' => 0,
            'per_page' => 50,
            'current_page' => 1,
            'last_page' => 0,
            'data' => [],
        ], 200)]);

        $this->artisan('collect:prefeitura-payroll', [
            '--year' => '2026',
            '--month' => '7',
            '--max-pages' => '1',
            '--per-page' => '50',
        ])->assertSuccessful();

        $this->artisan('collect:prefeitura-payroll', ['--month' => '13'])
            ->expectsOutput('O mês deve estar entre 1 e 12.')
            ->assertExitCode(2);

        $this->assertDatabaseHas('collector_runs', ['status' => 'partial']);
    }

    /** @param array<string, mixed>|null $pageOne */
    private function fakePaginatedPayroll(?array $pageOne = null): void
    {
        $pageOne ??= $this->fixture('payroll-page-1.json');
        $pageTwo = $this->fixture('payroll-page-2.json');

        Http::fake(function (Request $request) use ($pageOne, $pageTwo) {
            return Http::response(
                (int) $request['page'] === 2 ? $pageTwo : $pageOne,
                200,
                ['Content-Type' => 'application/json', 'ETag' => '"fixture"'],
            );
        });
    }

    /** @return array<string, mixed> */
    private function fixture(string $name): array
    {
        return json_decode(
            (string) file_get_contents(base_path('tests/Fixtures/prefeitura/'.$name)),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
