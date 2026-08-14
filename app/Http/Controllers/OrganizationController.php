<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function index(): Response
    {
        $organizations = Organization::query()
            ->where('is_current', true)
            ->with('parent:id,name,public_slug')
            ->orderByRaw("CASE WHEN type = 'secretariat' THEN 0 WHEN type = 'department' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get()
            ->map(fn (Organization $organization): array => $this->summary($organization));

        return Inertia::render('Organizations/Index', [
            'organizations' => $organizations,
            'lastUpdatedAt' => Organization::query()->where('is_current', true)->max('source_updated_at'),
        ]);
    }

    public function show(Organization $organization): Response
    {
        abort_unless($organization->is_current, 404);
        $organization->load([
            'parent:id,name,public_slug',
            'children' => fn ($query) => $query->where('is_current', true)->orderBy('name'),
            'source:id,name,official_url,last_successful_at',
            'sourceRecord:id,source_url,fetched_at,source_updated_at,validation_status',
        ]);

        return Inertia::render('Organizations/Show', [
            'organization' => [
                ...$this->summary($organization),
                'responsibleName' => $organization->responsible_name,
                'phone' => $organization->phone,
                'email' => $organization->email,
                'address' => $organization->address,
                'competencies' => $organization->competencies,
                'openingHours' => $organization->opening_hours,
                'children' => $organization->children->map(fn (Organization $child): array => $this->summary($child)),
                'provenance' => [
                    'sourceName' => $organization->source->name,
                    'officialUrl' => $organization->sourceRecord->source_url,
                    'fetchedAt' => $organization->source->last_successful_at?->toIso8601String(),
                    'sourceUpdatedAt' => $organization->source_updated_at?->toIso8601String(),
                    'validationStatus' => $organization->sourceRecord->validation_status,
                ],
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function summary(Organization $organization): array
    {
        return [
            'slug' => $organization->public_slug,
            'name' => $organization->name,
            'type' => $organization->type,
            'parent' => $organization->parent ? [
                'name' => $organization->parent->name,
                'slug' => $organization->parent->public_slug,
            ] : null,
        ];
    }
}
