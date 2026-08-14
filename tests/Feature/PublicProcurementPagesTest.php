<?php

namespace Tests\Feature;

use App\Models\RawSourceRecord;
use App\Models\Source;
use App\Services\NormalizeProcurementData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicProcurementPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_relations_use_official_ids_and_mask_cpf(): void
    {
        $this->raw('fornecedores', 10, ['id' => 10, 'nome' => 'PESSOA FORNECEDORA', 'razao_social' => 'PESSOA FORNECEDORA', 'cpf_cnpj' => '123.456.789-01']);
        $this->raw('licitacoes', 20, ['id' => 20, 'numero' => '20/2026', 'objeto' => 'OBJETO TESTE', 'modalidade_descricao' => 'PREGÃO', 'valor_estimado' => 500]);
        $this->raw('contratos', 30, ['id' => 30, 'numero' => '30/2026', 'objeto' => 'OBJETO TESTE', 'contratada' => 'PESSOA FORNECEDORA', 'contratada_id' => 10, 'licitacao_id' => 20, 'valor' => 450]);
        app(NormalizeProcurementData::class)->handle();

        $this->get(route('suppliers.index', ['q' => 'PESSOA']))->assertOk()->assertInertia(fn (Assert $p) => $p->where('items.0.subtitle', '***.***.789-**'));
        $this->get('/contratos')->assertOk()->assertInertia(fn (Assert $p) => $p->where('items.0.valueCents', 45000));
        $this->get('/contrato/302026-30')->assertOk()->assertInertia(fn (Assert $p) => $p->where('record.supplier.slug', 'pessoa-fornecedora-10')->where('record.procurement.slug', '202026-20'));
    }

    private function raw(string $resource, int $id, array $payload): void
    {
        $source = Source::query()->create(['key' => 'alcobaca.prefeitura.'.$resource, 'name' => $resource, 'entity' => 'Prefeitura', 'municipality_ibge_code' => '2900801', 'base_url' => 'https://official.test', 'official_url' => 'https://official.test', 'status' => 'operational', 'enabled' => true]);
        RawSourceRecord::query()->create(['source_id' => $source->id, 'external_id' => (string) $id, 'checksum' => hash('sha256', json_encode($payload)), 'source_url' => 'https://official.test/'.$resource.'/'.$id, 'fetched_at' => now(), 'payload' => $payload, 'content_type' => 'application/json', 'http_status' => 200, 'collector' => 'test', 'collector_version' => '1', 'validation_status' => 'valid']);
    }
}
