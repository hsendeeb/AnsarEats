<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantRegistrationRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'operating_hours' => 'array',
        'is_open' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
