<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawSourceRecord extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fetched_at' => 'datetime',
            'source_updated_at' => 'datetime',
            'payload' => 'array',
            'validation_errors' => 'array',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function collectorRun(): BelongsTo
    {
        return $this->belongsTo(CollectorRun::class);
    }
}
