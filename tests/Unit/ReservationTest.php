<?php

declare(strict_types=1);

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
}
