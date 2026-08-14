<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Inertia\Inertia;
use Inertia\Response;

class SourceController extends Controller
{
    public function index(): Response
    {
        $sources = Source::query()
            ->with(['healthChecks' => fn ($query) => $query->latest('checked_at')->limit(1)])
            ->orderBy('name')
            ->get()
            ->map(function (Source $source): array {
                $health = $source->healthChecks->first();

                return [
                    'key' => $source->key,
                    'name' => $source->name,
                    'entity' => $source->entity,
                    'officialUrl' => $source->official_url,
                    'status' => $source->status,
                    'lastSuccessfulAt' => $source->last_successful_at?->toIso8601String(),
                    'lastCheckedAt' => $health?->checked_at?->toIso8601String(),
                    'httpStatus' => $health?->http_status,
                    'recordsCount' => $health?->records_count,
                    'message' => $health?->message,
                ];
            });

        return Inertia::render('Sources/Index', ['sources' => $sources]);
    }
}
