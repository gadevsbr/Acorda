<?php

namespace Tests\Feature;

use App\Models\Source;
use App\Models\SourceHealthCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SourcesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_sources_page_exposes_provenance_and_latest_health(): void
    {
        $source = Source::query()->create([
            'key' => 'test.official',
            'name' => 'Fonte oficial de teste',
            'entity' => 'Entidade pública de teste',
            'base_url' => 'https://www.acessoinformacao.com.br/api',
            'official_url' => 'https://www.acessoinformacao.com.br/',
            'status' => 'partial',
            'enabled' => true,
        ]);
        SourceHealthCheck::query()->create([
            'source_id' => $source->id,
            'checked_at' => now(),
            'status' => 'partial',
            'http_status' => 200,
            'records_count' => 0,
            'message' => 'Resposta válida sem registros.',
        ]);

        $this->get('/fontes')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Sources/Index')
                ->has('sources', 1)
                ->where('sources.0.key', 'test.official')
                ->where('sources.0.status', 'partial')
                ->where('sources.0.httpStatus', 200)
                ->where('sources.0.recordsCount', 0)
            );
    }
}
