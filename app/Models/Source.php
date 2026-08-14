<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    protected $fillable = [
        'key',
        'name',
        'entity',
        'municipality_ibge_code',
        'base_url',
        'official_url',
        'status',
        'enabled',
        'metadata',
        'last_successful_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'metadata' => 'array',
            'last_successful_at' => 'datetime',
        ];
    }

    public function rawRecords(): HasMany
    {
        return $this->hasMany(RawSourceRecord::class);
    }

    public function collectorRuns(): HasMany
    {
        return $this->hasMany(CollectorRun::class);
    }

    public function healthChecks(): HasMany
    {
        return $this->hasMany(SourceHealthCheck::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(SourceAlert::class);
    }
}
