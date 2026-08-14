<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procurement extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['publication_date' => 'date', 'event_date' => 'date', 'situations' => 'array'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_slug';
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function sourceRecord()
    {
        return $this->belongsTo(RawSourceRecord::class, 'source_record_id');
    }
}
