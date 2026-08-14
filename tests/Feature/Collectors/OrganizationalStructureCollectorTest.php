<?php

namespace Tests\Feature\Collectors;

use App\Collectors\Alcobaca\Prefeitura\OrganizationalStructureCollector;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrganizationalStructureCollectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_preserves_raw_records_and_normalizes_exact_hierarchy(): void
    {
        $this->fakeStructure();

        $result = app(OrganizationalStructureCollector::class)->collect();

        $this->assertSame('success', $result->status);
        $this->assertSame(2, $result->created);
        $this->assertSame(2, $result->normalized);
        $this->assertDatabaseCount('raw_source_records', 2);
        $this->assertDatabaseCount('organizations', 2);
        $this->assertDatabaseHas('sources', [
            'key' => 'alcobaca.prefeitura.organizational-structure',
            'status' => 'operational',
        ]);

        $secretariat = Organization::query()->where('external_id', '53')->sole();
        $department = Organization::query()->where('external_id', '1180')->sole();
        $this->assertSame('secretariat', $secretariat->type);
        $this->assertSame('department', $department->type);
        $this->assertSame($secretariat->id, $department->parent_id);
        $this->assertSame('SECRETARIA DE FINANCAS', $secretariat->normalized_name);
        $this->assertSame('RESPONSÁVEL REMOVIDO NA FIXTURE', $department->responsible_name);
        $this->assertNotNull($department->source_record_id);
    }

    public function test_reprocessing_is_idempotent_for_raw_and_normalized_records(): void
    {
        $this->fakeStructure();
        $collector = app(OrganizationalStructureCollector::class);
        $collector->collect();
        $result = $collector->collect();

        $this->assertSame(0, $result->created);
        $this->assertSame(2, $result->unchanged);
        $this->assertDatabaseCount('raw_source_records', 2);
        $this->assertDatabaseCount('organizations', 2);
    }

    public function test_invalid_records_remain_raw_but_are_not_normalized(): void
    {
        $fixture = $this->fixture();
        unset($fixture['data'][1]['nome']);
        Http::fake(['*' => Http::response($fixture, 200, ['Content-Type' => 'application/json'])]);

        $result = app(OrganizationalStructureCollector::class)->collect();

        $this->assertSame('partial', $result->status);
        $this->assertSame(1, $result->invalid);
        $this->assertDatabaseCount('raw_source_records', 2);
        $this->assertDatabaseCount('organizations', 1);
        $this->assertDatabaseHas('raw_source_records', [
            'external_id' => '1180',
            'validation_status' => 'invalid',
        ]);
    }

    private function fakeStructure(): void
    {
        Http::fake(['*' => Http::response($this->fixture(), 200, ['Content-Type' => 'application/json'])]);
    }

    /** @return array<string, mixed> */
    private function fixture(): array
    {
        return json_decode(
            (string) file_get_contents(base_path('tests/Fixtures/prefeitura/organizational-structure-page-1.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
