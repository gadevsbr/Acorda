<?php

namespace App\Services;

use App\Models\IdentityCandidate;
use App\Models\Person;

final class IdentityCandidateGenerator
{
    /** @return array{groups:int,candidates:int,created:int} */
    public function generate(): array
    {
        $groups = Person::query()->select('normalized_name')->groupBy('normalized_name')->havingRaw('COUNT(*) > 1')->pluck('normalized_name');
        $candidates = 0;
        $created = 0;

        foreach ($groups as $normalizedName) {
            $people = Person::query()->where('normalized_name', $normalizedName)->orderBy('id')->get();
            for ($left = 0; $left < $people->count(); $left++) {
                for ($right = $left + 1; $right < $people->count(); $right++) {
                    $candidate = IdentityCandidate::query()->firstOrCreate(
                        ['left_person_id' => $people[$left]->id, 'right_person_id' => $people[$right]->id],
                        ['reason' => 'exact_normalized_name', 'evidence' => [
                            'normalized_name' => $normalizedName,
                            'left_registration' => $people[$left]->external_id,
                            'right_registration' => $people[$right]->external_id,
                        ]],
                    );
                    $candidates++;
                    if ($candidate->wasRecentlyCreated) {
                        $created++;
                    }
                }
            }
        }

        return ['groups' => $groups->count(), 'candidates' => $candidates, 'created' => $created];
    }
}
