<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'number', 'total_seats',
    ];

    public function classes(): HasMany
    {
        return $this->hasMany(BusClass::class);
    }
}
