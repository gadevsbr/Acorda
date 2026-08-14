<?php

namespace Tests\Feature\Collectors;

use App\Collectors\Alcobaca\Prefeitura\ProcurementCollector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProcurementCollectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_preserves_each_procurement_resource_without_cross_contamination(): void
    {
        $fixtures = [
            'fornecedores' => ['id' => 1, 'nome' => 'FORNECEDOR TESTE'],
            'contratos' => ['id' => 2, 'numero' => '2/2026', 'contratada' => 'FORNECEDOR TESTE', 'objeto' => 'OBJETO', 'valor' => 100],
            'licitacoes' => ['id' => 3, 'numero' => '3/2026', 'objeto' => 'OBJETO', 'modalidade_descricao' => 'PREGÃO'],
        ];
        Http::fake(function ($request) use ($fixtures) {
            $resource = collect(array_keys($fixtures))->first(fn (string $name): bool => str_contains($request->url(), '/'.$name.'?'));

            return Http::response(['total' => 1, 'per_page' => 100, 'current_page' => 1, 'last_page' => 1, 'data' => [$fixtures[$resource]]], 200);
        });
        foreach (array_keys($fixtures) as $resource) {
            $result = app(ProcurementCollector::class)->collect($resource);
            $this->assertSame('success', $result->status);
        }
        $this->assertDatabaseCount('sources', 3);
        $this->assertDatabaseCount('raw_source_records', 3);
    }

    public function test_empty_fiscal_source_is_partial(): void
    {
        Http::fake(['*' => Http::response(['total' => 0, 'per_page' => 100, 'current_page' => 1, 'last_page' => 0, 'data' => []], 200)]);
        $this->assertSame('partial', app(ProcurementCollector::class)->collect('fiscais-contrato')->status);
    }
}
