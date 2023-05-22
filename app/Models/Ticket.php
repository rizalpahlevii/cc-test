<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'transaction_number', 'user_id', 'agent_id', 'trip_id', 'total', 'purchase_date', 'payment_status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TicketDetail::class);
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status == 'unpaid';
    }

    public function isPaid(): bool
    {
        return $this->payment_status == 'paid';
    }

    public function isCanceled(): bool
    {
        return $this->payment_status == 'canceled';
    }
}
