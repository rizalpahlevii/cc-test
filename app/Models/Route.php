<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_city', 'destination_city',
    ];

    public function stops(): HasMany
    {
        return $this->hasMany(RouteStop::class);
    }
}
