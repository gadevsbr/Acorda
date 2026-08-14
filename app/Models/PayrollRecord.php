<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRecord extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'admission_date' => 'date', 'termination_date' => 'date', 'is_latest' => 'boolean',
            'gross_cents' => 'integer', 'deductions_cents' => 'integer', 'net_cents' => 'integer',
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

    public function employment(): BelongsTo
    {
        return $this->belongsTo(Employment::class);
    }

    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_id');
    }
}
