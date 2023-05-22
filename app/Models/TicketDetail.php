<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketDetail extends Model
{
    protected $fillable = [
        'ticket_code', 'ticket_id', 'bus_class_id', 'seat_number', 'price', 'total_price', 'passenger_name', 'passenger_email'
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function busClass(): BelongsTo
    {
        return $this->belongsTo(BusClass::class);
    }


}
