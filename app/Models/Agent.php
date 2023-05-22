<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
