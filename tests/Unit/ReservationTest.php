<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Billing\FakePaymentGateway;
use App\Models\Concert;
use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Billing\FakePaymentGatewayTest;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

   /** @test **/
   public function it_can_calculate_the_total_price_of_tickets()
   {
       $tickets = collect([
           (object) ['price' => 1300],
           (object) ['price' => 1300],
           (object) ['price' => 1300],
       ]);

       $reservation = new Reservation($tickets, "jane@gmail.com");

       $this->assertEquals(3900, $reservation->totalCost());
   }

   /** @test **/
   public function receiving_tickets_from_a_reservation()
   {
       $reservation = new Reservation(collect(), "jane@gmail.com");

       $this->assertEquals(collect(), $reservation->tickets());
   }

   /** @test **/
   public function receiving_email_from_a_reservation()
   {
       $reservation = new Reservation(collect(), "jane@gmail.com");

       $this->assertEquals(collect(), $reservation->tickets());
       $this->assertEquals("jane@gmail.com", $reservation->email());
   }

   /** @test **/
   public function tickets_can_be_released_when_reservation_is_cancelled() :void
   {
       $tickets = collect([
           \Mockery::spy(Ticket::class),
           \Mockery::spy(Ticket::class),
           \Mockery::spy(Ticket::class),

       ]);

       $reservation = new Reservation($tickets, "jane@gmail.com");

       $reservation->cancel();

       foreach($tickets as $ticket) {
           $ticket->shouldHaveReceived('release');
       }
   }

    /** @test **/
    public function reservation_can_complete_an_order()
    {
        $concert = Concert::factory()->create(['ticket_price' => 1200]);
        $tickets = Ticket::factory(3)->create(['concert_id' => $concert->id]);
        $reservation = new Reservation($tickets, "janedoe@example.com");
        $paymentGateway = new FakePaymentGateway();

        $order = $reservation->complete($paymentGateway, $paymentGateway->getValidToken());

        $this->assertEquals(3600, $order->amount);
        $this->assertEquals("janedoe@example.com", $order->email);
        $this->assertEquals(3, $order->ticketsQuantity());
        $this->assertEquals(3600, $paymentGateway->totalCharges());

    }
}
