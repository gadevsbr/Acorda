<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['signature_date' => 'date', 'start_date' => 'date', 'end_date' => 'date'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_slug';
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
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
