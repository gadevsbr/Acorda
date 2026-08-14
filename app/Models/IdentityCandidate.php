<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdentityCandidate extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['evidence' => 'array', 'reviewed_at' => 'datetime'];
    }

    public function leftPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'left_person_id');
    }

    public function rightPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'right_person_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
