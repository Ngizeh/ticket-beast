<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class OrderTest extends TestCase
{
    /** @test **/
    public function it_belongs_a_concert()
    {
        $concert = Concert::factory()->create(['ticket_price' => 1200])->addTickets(5);
        $order = $concert->orderTickets('janedoe@example.com', 5);

        $concert = $order->concert;

        $this->assertInstanceOf(Concert::class, $concert);
    }
    /** @test **/
    public function can_convert_the_order_to_an_array()
    {
        $concert = Concert::factory()->create(['ticket_price' => 1200])->addTickets(5);
        $order = $concert->orderTickets('janedoe@example.com', 5);

        $results = $order->toArray();

        $this->assertEquals([
            'email' => 'janedoe@example.com',
            'ticket_quantity' => 5,
            'amount' => 6000
        ], $results);
    }
    /** @test **/
    public function can_have_many_tickets()
    {
        $concert = Concert::factory()->create()->addTickets(5);

        $tickets = $concert->tickets;

        $this->assertInstanceOf(Collection::class, $tickets);
    }

    /** @test **/
    public function can_cancel_ordered_tickets()
    {
        $concert = Concert::factory()->create()->addTickets(5);
        $order = $concert->orderTickets('janedoe@example.com', 4);

        $order->cancel();

        $this->assertEquals(5, $concert->ticketsRemaining());
        $this->assertNull(Order::find($order->id));
    }
}
