<?php

namespace Tests\Feature\Collectors;

use App\Collectors\Alcobaca\Prefeitura\ExpenseCollector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExpenseCollectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_preserves_valid_expenses_idempotently(): void
    {
        Http::fake(['*' => Http::response($this->page([[
            'des_codigo' => 91, 'des_data' => '2026-07-10', 'des_unidade_gestora' => 'PREFEITURA',
            'des_credor' => 'FORNECEDOR DA FIXTURE', 'des_fase' => 'PAGAMENTO', 'des_valor' => 123.45,
            'des_servico_prestado' => 'SERVIÇO DA FIXTURE', 'diaria' => false,
        ]]), 200, ['Content-Type' => 'application/json'])]);
        $collector = app(ExpenseCollector::class);
        $first = $collector->collect();
        $second = $collector->collect();
        $this->assertSame('success', $first->status);
        $this->assertSame(1, $first->created);
        $this->assertSame(1, $second->unchanged);
        $this->assertDatabaseCount('raw_source_records', 1);
        $this->assertDatabaseHas('raw_source_records', ['external_id' => '91', 'validation_status' => 'valid']);
    }

    public function test_empty_source_is_partial_and_does_not_claim_no_expenses(): void
    {
        Http::fake(['*' => Http::response($this->page([]), 200)]);
        $result = app(ExpenseCollector::class)->collect();
        $this->assertSame('partial', $result->status);
        $this->assertDatabaseHas('source_health_checks', ['status' => 'partial', 'records_count' => 0]);
    }

    private function page(array $data): array
    {
        $count = count($data);

        return ['total' => $count, 'per_page' => 100, 'current_page' => 1, 'last_page' => $count ? 1 : 0, 'data' => $data];
    }
}
