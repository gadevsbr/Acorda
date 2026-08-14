<?php

namespace App\Http\Controllers;

use App\Models\Employment;
use App\Models\PayrollRecord;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PersonController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Str::of((string) $request->query('q'))->trim()->squish()->limit(100)->toString();
        $normalized = Str::of($query)->ascii()->upper()->toString();

        $people = collect();
        if (mb_strlen($normalized) >= 2) {
            $people = Person::query()
                ->where(function ($builder) use ($normalized): void {
                    $builder->where('normalized_name', 'like', '%'.$normalized.'%')
                        ->orWhere('external_id', 'like', $normalized.'%');
                })
                ->with(['employments' => fn ($builder) => $builder
                    ->with('position:id,name')
                    ->orderByDesc('is_current')
                    ->orderByDesc('last_seen_at')])
                ->orderBy('name')
                ->orderBy('external_id')
                ->limit(50)
                ->get()
                ->map(fn (Person $person): array => $this->summary($person));
        }

        return Inertia::render('People/Index', [
            'query' => $query,
            'minimumQueryLength' => 2,
            'results' => $people,
        ]);
    }

    public function show(Person $person): Response
    {
        $person->load([
            'source:id,name,official_url,last_successful_at',
            'sourceRecord:id,source_url,fetched_at,validation_status',
            'employments' => fn ($builder) => $builder->with([
                'position:id,name',
                'organization:id,name,public_slug',
                'source:id,name,official_url,last_successful_at',
                'sourceRecord:id,source_url,fetched_at,validation_status',
            ])->orderByDesc('is_current')->orderByDesc('last_seen_at'),
        ]);

        $employmentIds = $person->employments->pluck('id');
        $payroll = PayrollRecord::query()
            ->whereIn('employment_id', $employmentIds)
            ->with(['source:id,name,official_url,last_successful_at', 'sourceRecord:id,source_url,fetched_at,validation_status'])
            ->orderByDesc('reference_year')
            ->orderByDesc('reference_month')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (PayrollRecord $record): array => [
                'id' => $record->id,
                'registration' => $record->registration,
                'reference' => sprintf('%02d/%d', $record->reference_month, $record->reference_year),
                'calculationType' => $record->calculation_type,
                'grossCents' => $record->gross_cents,
                'deductionsCents' => $record->deductions_cents,
                'netCents' => $record->net_cents,
                'positionName' => $record->position_name,
                'workplace' => $record->workplace,
                'isLatest' => $record->is_latest,
                'supersedesId' => $record->supersedes_id,
                'provenance' => $this->provenance($record->source, $record->sourceRecord),
            ]);

        return Inertia::render('People/Show', [
            'person' => [
                ...$this->summary($person),
                'employments' => $person->employments->map(fn (Employment $employment): array => [
                    'registration' => $employment->registration,
                    'position' => $employment->position?->name,
                    'organization' => $employment->organization ? [
                        'name' => $employment->organization->name,
                        'slug' => $employment->organization->public_slug,
                    ] : null,
                    'costCenter' => $employment->cost_center,
                    'regime' => $employment->employment_regime,
                    'monthlyWorkload' => $employment->monthly_workload,
                    'admissionDate' => $employment->admission_date?->toDateString(),
                    'isCurrent' => $employment->is_current,
                    'provenance' => $this->provenance($employment->source, $employment->sourceRecord),
                ]),
                'payroll' => $payroll,
                'provenance' => $this->provenance($person->source, $person->sourceRecord),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function summary(Person $person): array
    {
        $employment = $person->employments->first();

        return [
            'slug' => $person->public_slug,
            'name' => $person->name,
            'registration' => $person->external_id,
            'position' => $employment?->position?->name,
            'regime' => $employment?->employment_regime,
            'isCurrent' => $employment?->is_current ?? false,
        ];
    }

    /** @return array<string, mixed> */
    private function provenance(object $source, object $record): array
    {
        return [
            'sourceName' => $source->name,
            'officialUrl' => $record->source_url ?: $source->official_url,
            'fetchedAt' => $record->fetched_at?->toIso8601String(),
            'validationStatus' => $record->validation_status,
        ];
    }
}
