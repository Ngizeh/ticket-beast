<?php

namespace Tests\Unit;

use App\Models\Concert;
use Tests\TestCase;

class TicketsTest extends TestCase
{
   /** @test **/
   public function scope_availability_of_tickets_in_reference_to_order()
   {
       $concert = Concert::factory()->create()->addTickets(10);

       $tickersAvailable = $concert->tickets()->available();

       $this->assertEquals($concert->ticketsRemaining(), $tickersAvailable->count());
   }

   /** @test **/
   public function tickets_can_be_released()
   {
       $concert = Concert::factory()->create()->addTickets(10);
       $order = $concert->orderTickets('jane@example.com', 5);
       $ticket = $order->tickets()->first();
       $this->assertEquals($order->id, $ticket->order_id);

       $ticket->release();

       $this->assertNull($ticket->fresh()->order_id);
   }
}
