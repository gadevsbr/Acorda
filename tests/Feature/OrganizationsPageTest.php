<?php

namespace Tests\Feature;

use App\Collectors\Alcobaca\Prefeitura\OrganizationalStructureCollector;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrganizationsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_organizations_index_uses_normalized_official_records(): void
    {
        $this->collectFixture();

        $this->get('/orgaos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Organizations/Index')
                ->has('organizations', 2)
                ->where('organizations.1.name', 'Departamento de Tributação')
                ->where('organizations.1.parent.name', 'SECRETARIA DE FINANÇAS')
            );
    }

    public function test_an_organization_page_exposes_context_and_provenance(): void
    {
        $this->collectFixture();
        $department = Organization::query()->where('external_id', '1180')->sole();

        $this->get(route('organizations.show', $department))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Organizations/Show')
                ->where('organization.name', 'Departamento de Tributação')
                ->where('organization.parent.name', 'SECRETARIA DE FINANÇAS')
                ->where('organization.responsibleName', 'RESPONSÁVEL REMOVIDO NA FIXTURE')
                ->where('organization.provenance.validationStatus', 'valid')
                ->where('organization.provenance.sourceName', 'Prefeitura de Alcobaça — Estrutura Organizacional')
            );
    }

    private function collectFixture(): void
    {
        $fixture = json_decode(
            (string) file_get_contents(base_path('tests/Fixtures/prefeitura/organizational-structure-page-1.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        Http::fake(['*' => Http::response($fixture, 200, ['Content-Type' => 'application/json'])]);
        app(OrganizationalStructureCollector::class)->collect();
    }
}
