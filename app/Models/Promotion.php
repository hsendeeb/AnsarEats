<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
