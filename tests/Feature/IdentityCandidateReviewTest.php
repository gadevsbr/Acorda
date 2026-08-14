<?php

namespace Tests\Feature;

use App\Models\CollectorRun;
use App\Models\IdentityCandidate;
use App\Models\Person;
use App\Models\RawSourceRecord;
use App\Models\Source;
use App\Models\User;
use App\Services\IdentityCandidateGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdentityCandidateReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_names_generate_idempotent_candidates_without_merging_people(): void
    {
        $this->person('100', 'MARIA DA SILVA');
        $this->person('101', 'MARIA DA SILVA');
        $this->person('102', 'OUTRA PESSOA');
        $generator = app(IdentityCandidateGenerator::class);

        $first = $generator->generate();
        $second = $generator->generate();

        $this->assertSame(['groups' => 1, 'candidates' => 1, 'created' => 1], $first);
        $this->assertSame(0, $second['created']);
        $this->assertDatabaseCount('people', 3);
        $this->assertDatabaseCount('identity_candidates', 1);
        $this->assertDatabaseHas('identity_candidates', ['status' => 'pending', 'reason' => 'exact_normalized_name']);
    }

    public function test_review_requires_authentication_and_records_the_reviewer_without_merging(): void
    {
        $left = $this->person('100', 'MARIA DA SILVA');
        $right = $this->person('101', 'MARIA DA SILVA');
        app(IdentityCandidateGenerator::class)->generate();
        $candidate = IdentityCandidate::query()->sole();

        $this->patch(route('identity-candidates.update', $candidate), ['status' => 'confirmed', 'review_notes' => 'Mesma pessoa confirmada documentalmente.'])
            ->assertRedirect(route('login'));

        $reviewer = User::factory()->create();
        $this->actingAs($reviewer)->patch(route('identity-candidates.update', $candidate), [
            'status' => 'confirmed', 'review_notes' => 'Mesma pessoa confirmada documentalmente.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('identity_candidates', ['id' => $candidate->id, 'status' => 'confirmed', 'reviewed_by' => $reviewer->id]);
        $this->assertDatabaseCount('people', 2);
        $this->assertNotSame($left->id, $right->id);
    }

    public function test_review_page_is_private(): void
    {
        $this->get(route('identity-candidates.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('identity-candidates.index'))->assertOk();
    }

    private function person(string $externalId, string $name): Person
    {
        $source = Source::query()->firstOrCreate(['key' => 'test.people'], [
            'name' => 'Teste', 'entity' => 'Teste', 'base_url' => 'https://example.test',
            'official_url' => 'https://example.test', 'status' => 'operational', 'enabled' => true,
        ]);
        $run = CollectorRun::query()->create([
            'source_id' => $source->id, 'collector' => 'test', 'collector_version' => '1',
            'status' => 'success', 'started_at' => now(), 'finished_at' => now(),
        ]);
        $raw = RawSourceRecord::query()->create([
            'source_id' => $source->id, 'collector_run_id' => $run->id, 'external_id' => $externalId,
            'source_url' => 'https://example.test', 'fetched_at' => now(), 'payload' => ['name' => $name],
            'checksum' => hash('sha256', $externalId), 'http_status' => 200, 'collector' => 'test',
            'collector_version' => '1', 'validation_status' => 'valid',
        ]);

        return Person::query()->create([
            'source_id' => $source->id, 'source_record_id' => $raw->id, 'external_id' => $externalId,
            'public_slug' => strtolower(str_replace(' ', '-', $name)).'-'.$externalId,
            'name' => $name, 'normalized_name' => $name, 'municipality_ibge_code' => '2900801',
        ]);
    }
}
