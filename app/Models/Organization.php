<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'source_updated_at' => 'datetime',
            'is_current' => 'boolean',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function sourceRecord(): BelongsTo
    {
        return $this->belongsTo(RawSourceRecord::class, 'source_record_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function getRouteKeyName(): string
    {
        return 'public_slug';
    }
}
