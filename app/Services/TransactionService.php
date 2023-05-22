<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\BusClass;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\Trip;
use DB;
use Exception;
use Log;
use Throwable;

class TransactionService
{
    /**
     * Make transaction
     *
     * @param Agent $agent
     * @param Trip $trip
     * @param array $passengers
     * @return Ticket
     * @throws Exception
     * @throws Throwable
     */
    public function makeTransaction(
        Agent $agent,
        Trip  $trip,
        array $passengers,
    ): Ticket
    {
        DB::beginTransaction();
        try {
            $ticket = Ticket::create([
                'transaction_number' => 'T' . rand(100000, 999999),
                'user_id' => auth()->id(),
                'agent_id' => $agent->id,
                'trip_id' => $trip->id,
                'total' => 0,
                'purchase_date' => now(),
                'payment_status' => 'unpaid',
            ]);

            foreach ($passengers as $passenger) {
                $busClass = BusClass::find($passenger['bus_class_id']);
                if (!$busClass) {
                    throw new Exception('Bus class not found');
                }

                if (!$this->checkAvailability($trip, $passenger)) {
                    continue;
                }

                $ticket->details()->create([
                    'passenger_name' => $passenger['passenger_name'],
                    'passenger_email' => $passenger['passenger_email'],
                    'bus_class_id' => $passenger['bus_class_id'],
                    'price' => $busClass->price,
                    'total_price' => $busClass->price,
                ]);
            }

            if ($ticket->details()->count() === 0) {
                $ticket->delete();
                throw new Exception('No passenger added because no more seat available', 422);
            }

            $ticket->update([
                'total' => $ticket->details()->sum('total_price'),
            ]);
            DB::commit();
            return $ticket->fresh();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

    }

    private function checkAvailability(
        Trip  $trip,
        array $passenger,
    ): bool
    {
        $class = BusClass::find($passenger['bus_class_id']);
        $usedSeat = $trip->ticketDetails()->where('bus_class_id', $passenger['bus_class_id'])
            ->whereNotNull('ticket_code')
            ->whereNotNull('seat_number')
            ->count();

        return !($usedSeat >= $class->total_seats);

    }

    /**
     * Pay transaction
     *
     * @param Ticket $ticket
     * @return Ticket
     */
    public function payTransaction(Ticket $ticket): Ticket
    {
        $ticket->update([
            'payment_status' => 'paid',
        ]);

        $this->handlePaidTicket($ticket->fresh());

        return $ticket->fresh();
    }

    private function handlePaidTicket(Ticket $ticket): void
    {
        $ticket->details->each(function (TicketDetail $ticketDetail) {
            // Send email to passenger
            if (!$this->checkAvailability($ticketDetail->ticket->trip, $ticketDetail->toArray())) {
                // If no more seat available, send email to admin
                Log::info('Send email to admin because no more seat available');
                $ticketDetail->delete();
            } else {
                $ticketDetail->update([
                    'ticket_code' => 'T' . rand(100000, 999999),
                    'seat_number' => $this->getSeatNumber($ticketDetail),
                ]);
                Log::info('Send email to passenger');
            }
        });

        // Send email to agent
        Log::info('Send email to agent');
        $ticket->total = $ticket->details()->sum('total_price');
        $ticket->save();
    }

    private function getSeatNumber(TicketDetail $ticketDetail): int
    {
        $ticket = $ticketDetail->ticket;
        $busClass = $ticketDetail->busClass;

        $usedSeat = TicketDetail::where('ticket_id', $ticket->id)
            ->where('bus_class_id', $busClass->id)
            ->whereNotNull('seat_number')
            ->count();

        return $usedSeat + 1;
    }

    public function cancelTransaction(Ticket $ticket): Ticket
    {
        $ticket->update([
            'payment_status' => 'canceled',
        ]);

        $ticket->details->each(function (TicketDetail $ticketDetail) {
            $ticketDetail->update([
                'ticket_code' => null,
                'seat_number' => null,
            ]);
        });

        return $ticket->fresh();
    }
}
