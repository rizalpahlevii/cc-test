<?php

namespace Tests\Unit;

use App\Models\{Agent, Bus, BusClass, Ticket, TicketDetail, Trip, User};
use App\Services\TransactionService;
use Exception;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use Tests\TestCase;
use Throwable;

class TransactionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private ?Agent $agent = null;

    private ?Bus $bus = null;

    private array $busClasses;

    private ?Trip $trip = null;

    private array $passengers = [];

    /**
     * Test successfully make transaction
     *
     * @throws Exception|Throwable
     */
    public function test_successfully_make_transaction(): void
    {
        $this->prepareData();
        $service = new TransactionService();
        $ticket = $service->makeTransaction(
            $this->agent,
            $this->trip,
            $this->passengers,
        );

        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseCount('ticket_details', count($this->passengers));
        $this->assertTrue($ticket->isUnpaid());
        foreach ($this->passengers as $passenger) {
            $passenger['ticket_id'] = $ticket->id;
            $this->assertDatabaseHas('ticket_details', $passenger);
        }
    }

    /**
     * Prepare data
     *
     * @return void
     */
    private function prepareData(): void
    {
        $passengerCount = $this->faker->numberBetween(1, 5);
        $passengers = [];

        $this->actingAs(User::factory()->create());

        $agent = Agent::factory()->create();
        $bus = Bus::factory()->create();
        $classes = [
            BusClass::factory()->for($bus)->economy()->create(),
            BusClass::factory()->for($bus)->business()->create(),
            BusClass::factory()->for($bus)->vip()->create(),
        ];
        $trip = Trip::factory()->for($bus)->create();

        $this->agent = $agent;
        $this->bus = $bus;
        $this->busClasses = $classes;
        $this->trip = $trip;

        for ($i = 0; $i < $passengerCount; $i++) {
            $passengers[] = [
                'passenger_name' => $this->faker->name,
                'passenger_email' => $this->faker->safeEmail,
                'bus_class_id' => $this->faker->randomElement($classes)->id,
            ];
        }
        $this->passengers = $passengers;


    }

    /**
     * Test successfully make transaction but no seat available
     *
     * @throws Throwable
     */
    public function test_make_transaction_but_no_seat_available(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No passenger added because no more seat available');
        $this->expectExceptionCode(422);
        $this->prepareData();
        $service = new TransactionService();

        $this->setAllDataToNotAvailable();

        $service->makeTransaction(
            $this->agent,
            $this->trip,
            $this->passengers,
        );
    }

    /**
     * Set all data to not available
     *
     * @return void
     */
    private function setAllDataToNotAvailable(): void
    {
        $ticket = Ticket::create([
            'trip_id' => $this->trip->id,
            'transaction_number' => 'T' . rand(100000, 999999),
            'user_id' => auth()->id(),
            'agent_id' => $this->agent->id,
            'total' => 0,
            'purchase_date' => now(),
            'payment_status' => 'paid',
        ]);
        foreach ($this->busClasses as $busClass) {
            for ($i = 0; $i < $busClass->total_seats; $i++) {
                TicketDetail::create([
                    'ticket_id' => $ticket->id,
                    'bus_class_id' => $busClass->id,
                    'seat_number' => $i + 1,
                    'ticket_code' => 'T' . rand(100000, 999999),
                    'passenger_name' => $this->faker->name,
                    'passenger_email' => $this->faker->safeEmail,
                    'price' => $busClass->price,
                    'total_price' => $busClass->price,
                ]);
            }
        }
    }

    /**
     * Test successfully make transaction but partially seat not available
     *
     * @throws Throwable
     */
    public function test_make_transaction_but_partially_seat_not_available(): void
    {
        $this->prepareData();

        $this->setPartiallyDataToNotAvailable();

        $service = new TransactionService();
        $ticket = $service->makeTransaction(
            $this->agent,
            $this->trip,
            $this->passengers,
        );
        $this->assertTrue($ticket->isUnpaid());
        $this->assertFalse(TicketDetail::where('ticket_id', $ticket->id)->count() === count($this->passengers));
    }

    /**
     * Set all data to not available
     *
     * @return void
     */
    private function setPartiallyDataToNotAvailable(): void
    {
        $ticket = Ticket::create([
            'trip_id' => $this->trip->id,
            'transaction_number' => 'T' . rand(100000, 999999),
            'user_id' => auth()->id(),
            'agent_id' => $this->agent->id,
            'total' => 0,
            'purchase_date' => now(),
            'payment_status' => 'paid',
        ]);
        $passengers = [];
        $busClassCount = count($this->busClasses);
        $rand = rand(0, $busClassCount - 1);
        foreach ($this->busClasses as $index => $busClass) {
            if ($rand === $index) {
                $passengers[] = [
                    'passenger_name' => $this->faker->name,
                    'passenger_email' => $this->faker->safeEmail,
                    'bus_class_id' => $busClass->id,
                ];
                continue;
            }
            for ($i = 0; $i < $busClass->total_seats; $i++) {
                TicketDetail::create([
                    'ticket_id' => $ticket->id,
                    'bus_class_id' => $busClass->id,
                    'seat_number' => $i + 1,
                    'ticket_code' => 'T' . rand(100000, 999999),
                    'passenger_name' => $this->faker->name,
                    'passenger_email' => $this->faker->safeEmail,
                    'price' => $busClass->price,
                    'total_price' => $busClass->price,
                ]);
            }
            $passengers[] = [
                'passenger_name' => $this->faker->name,
                'passenger_email' => $this->faker->safeEmail,
                'bus_class_id' => $busClass->id,
            ];
        }
        $this->passengers = $passengers;
    }

    /**
     * Test successfully pay transaction
     *
     * @throws Exception|Throwable
     */
    public function test_successfully_pay_transaction(): void
    {
        $this->prepareData();
        $service = new TransactionService();
        $ticket = $service->makeTransaction(
            $this->agent,
            $this->trip,
            $this->passengers,
        );
        $this->assertTrue($ticket->isUnpaid());

        $ticket = $service->payTransaction($ticket);
        $this->assertTrue($ticket->isPaid());
        foreach ($ticket->details as $detail) {
            $this->assertNotNull($detail->ticket_code);
            $this->assertNotNull($detail->seat_number);
        }
    }

    /**
     * Test successfully pay transaction but partially seat not available
     *
     * @throws Throwable
     */
    public function test_successfully_pay_transaction_but_partially_seat_not_available(): void
    {
        $this->prepareData();
        $service = new TransactionService();
        $ticket = $service->makeTransaction(
            $this->agent,
            $this->trip,
            $this->passengers,
        );
        $this->assertTrue($ticket->isUnpaid());

        $this->setPartiallyDataToNotAvailable();

        $ticket = $service->payTransaction($ticket);
        $this->assertTrue($ticket->isPaid());

        $this->assertFalse(TicketDetail::where('ticket_id', $ticket->id)->count() === count($this->passengers));
    }

    /**
     * Test successfully cancel transaction
     *
     * @throws Throwable
     */
    public function test_successfully_cancel_transaction(): void
    {
        $this->prepareData();
        $service = new TransactionService();
        $ticket = $service->makeTransaction(
            $this->agent,
            $this->trip,
            $this->passengers,
        );
        $this->assertTrue($ticket->isUnpaid());

        $ticket = $service->cancelTransaction($ticket);
        $this->assertTrue($ticket->isCanceled());
    }
}
