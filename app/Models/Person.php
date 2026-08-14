<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $guarded = [];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function sourceRecord(): BelongsTo
    {
        return $this->belongsTo(RawSourceRecord::class, 'source_record_id');
    }

    public function employments(): HasMany
    {
        return $this->hasMany(Employment::class);
    }

    public function getRouteKeyName(): string
    {
        return 'public_slug';
    }
}
