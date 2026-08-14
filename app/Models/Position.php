<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $guarded = [];

    public function employments(): HasMany
    {
        return $this->hasMany(Employment::class);
    }
}
