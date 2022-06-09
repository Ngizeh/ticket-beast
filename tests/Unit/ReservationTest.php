<?php

namespace Tests\Unit;

use App\Models\Reservation;
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
}
