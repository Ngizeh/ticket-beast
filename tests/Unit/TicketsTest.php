<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketsTest extends TestCase
{
    use RefreshDatabase;

   /** @test **/
   public function scope_availability_of_tickets_in_reference_to_order()
   {
       $concert = Concert::factory()->create()->addTickets(10);

       $tickersAvailable = $concert->tickets()->available();

       $this->assertEquals($concert->ticketsRemaining(), $tickersAvailable->count());
   }

   /** @test **/
   public function it_belongs_to_a_concert()
   {
       $concert = Concert::factory()->create()->addTickets(10);

       $tickets = $concert->tickets;

       $this->assertInstanceOf(Collection::class, $tickets);

   }

   /** @test **/
   public function tickets_can_be_released()
   {
       $ticket = Ticket::factory()->create(['reserved_at' => now()]);
       $this->assertNotNull($ticket->reserved_at);

       $ticket->release();

       $this->assertNull($ticket->fresh()->reserved_at);
   }

   /** @test **/
   public function ticket_can_be_reserved()
   {
       $ticket = Ticket::factory()->create();
       $this->assertNull($ticket->reserved_at);

       $ticket->reserve();
       $this->assertNotNull($ticket->fresh()->reserved_at);
   }
}
