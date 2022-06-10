<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class OrderTest extends TestCase
{
    /** @test **/
    public function it_belongs_to_many_concert_through_tickets()
    {
        $concert = Concert::factory()->create(['ticket_price' => 1200])->addTickets(5);
        $order = $concert->orderTickets('janedoe@example.com', 5);

        $concert = $order->concert;

        $this->assertInstanceOf(Collection::class, $concert);
    }

    /** @test **/
    public function it_can_finds_an_order_for_a_ticket()
    {
        $concert = Concert::factory()->create(['ticket_price' => 1200])->addTickets(5);
        $this->assertEquals(5, $concert->ticketsRemaining());


        $order = Order::forTickets($concert->findTickets(3), 'janedoe@example.com', 3600);

        $this->assertEquals(2, $concert->ticketsRemaining());
        $this->assertEquals(3, $order->ticketsQuantity());
        $this->assertEquals("janedoe@example.com", $order->email);
        $this->assertEquals(3600, $order->amount);

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
}
