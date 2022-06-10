<?php

namespace Tests\Unit;

use App\Models\Reservation;
use App\Models\Ticket;
use PHPUnit\Framework\TestCase;

class ReservationTest extends TestCase
{
   /** @test **/
   public function it_can_calculate_the_total_price_of_tickets()
   {
       $tickets = collect([
           (object) ['price' => 1300],
           (object) ['price' => 1300],
           (object) ['price' => 1300],
       ]);

       $reservation = new Reservation($tickets);

       $this->assertEquals(3900, $reservation->totalCost());
   }

   /** @test **/
   public function tickets_can_be_released_when_reservation_is_cancelled()
   {
       $tickets = collect([
           \Mockery::spy(Ticket::class),
           \Mockery::spy(Ticket::class),
           \Mockery::spy(Ticket::class),

       ]);

       $reservation = new Reservation($tickets);

       $reservation->cancel();

       foreach($tickets as $ticket) {
           $ticket->shouldHaveReceived('release');
       }
   }
}
