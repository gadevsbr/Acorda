<?php

namespace Tests\Feature\Collectors;

use App\Collectors\Support\SourceAlertManager;
use App\Models\Source;
use App\Models\SourceAlert;
use App\Models\SourceHealthCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceAlertManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_operational_health_check_resolves_open_alerts(): void
    {
        $source = Source::query()->create([
            'key' => 'fixture.source',
            'name' => 'Fonte de fixture',
            'entity' => 'Entidade de fixture',
            'base_url' => 'https://www.acessoinformacao.com.br/api',
            'official_url' => 'https://www.acessoinformacao.com.br/',
            'status' => 'unavailable',
            'enabled' => true,
        ]);
        SourceAlert::query()->create([
            'source_id' => $source->id,
            'type' => 'source_unavailable',
            'severity' => 'critical',
            'status' => 'open',
            'occurrences' => 1,
            'first_detected_at' => now()->subMinute(),
            'last_detected_at' => now()->subMinute(),
            'message' => 'Indisponível.',
        ]);
        $health = SourceHealthCheck::query()->create([
            'source_id' => $source->id,
            'checked_at' => now(),
            'status' => 'operational',
            'http_status' => 200,
            'records_count' => 3,
        ]);

        app(SourceAlertManager::class)->evaluate($source, $health);

        $alert = SourceAlert::query()->sole();
        $this->assertSame('resolved', $alert->status);
        $this->assertNotNull($alert->resolved_at);
    }
}
