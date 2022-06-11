<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Concert;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\NotEnoughTicketsRemainingException;

class ConcertTest extends TestCase
{

    /** @test **/
    public function can_get_the_formatted_date()
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2022-04-05')
        ]);

        $date = $concert->formatted_date;

        $this->assertEquals('April 05, 2022', $date);
    }

    /** @test **/
    public function can_get_the_formatted_opening_time()
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2022-04-05 18:00')
        ]);

        $opening_time = $concert->opens_at;

        $this->assertEquals('6:00pm', $opening_time);
    }

    /** @test **/
    public function can_get_formatted_ticket_price()
    {
        $concert = Concert::factory()->make(['ticket_price' => 3250]);

        $price = $concert->formatted_ticket_price;

        $this->assertEquals('32.50', $price);
    }

    /** @test **/
    public function concerts_with_published_at_date_are_published_or_viewable()
    {
        $concertA = Concert::factory()->create(['published_at' => Carbon::now()->subWeek(-1)]);
        $concertB = Concert::factory()->create(['published_at' => Carbon::now()->subWeek(-2)]);
        $unpublished = Concert::factory()->create(['published_at' => null]);

        $concert = Concert::published()->get();

        $this->assertTrue($concert->contains($concertA));
        $this->assertTrue($concert->contains($concertB));
        $this->assertfalse($concert->contains($unpublished));
    }

    /** @test **/
    public function has_orders()
    {
        $concert = Concert::factory()->create();

        $orders = $concert->orders;

        $this->assertInstanceOf(Collection::class, $orders);
    }

    /** @test **/
    public function can_have_tickets()
    {
        $concert = Concert::factory()->create();

        $tickets = $concert->tickets;

        $this->assertInstanceOf(Collection ::class, $tickets);
    }

    /** @test **/
    public function concert_can_add_tickets()
    {
        $concert = Concert::factory()->create()->addTickets(20);

        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test **/
    public function tickets_remaining_does_not_associate_with_tickets_ordered()
    {
        $concert = Concert::factory()->create()->addTickets(50);

        $order =$concert->orderTickets('janedoe@example.com', 30);

        $this->assertEquals(20, $concert->ticketsRemaining());
        $this->assertCount(30, $order->tickets);
        $this->assertEquals('janedoe@example.com', $order->email);
    }

    /** @test **/
    public function can_not_order_tickets_more_than_remaining()
    {
        $this->expectException(NotEnoughTicketsRemainingException::class);

        $concert = Concert::factory()->create();

        $concert->addTickets(5);

        $concert->orderTickets('janedoe@example.com', 6);

    }


    /** @test **/
    public function trying_to_order_tickets_more_than_remaining_throws_an_exception()
    {
        $concert = Concert::factory()->create()->addTickets(10);

        try{
             $concert->orderTickets('janedoe@example.com', 6);
             $concert->orderTickets('johndoe@example.com', 6);
        }catch (NotEnoughTicketsRemainingException $e){
            $this->assertTrue($concert->hasOrderFor('janedoe@example.com'));
            $this->assertFalse($concert->hasOrderFor('johdoe@example.com'));
            $this->assertEquals(4, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Must fail');
    }

    /** @test **/
    public function can_reserve_a_ticket()
    {
        $concert = Concert::factory()->published()->create()->addTickets(3);
        $this->assertEquals(3, $concert->ticketsRemaining());

        $reserved = $concert->reserveTickets(2, "jane@gmail.com");

        $this->assertEquals(1, $concert->ticketsRemaining());
        $this->assertCount(2, $reserved->tickets());
        $this->assertEquals("jane@gmail.com", $reserved->email());
    }

    /** @test **/
    public function can_reserve_tickets_that_have_already_been_purchased()
    {
        $concert = Concert::factory()->create()->addTickets(5);

        $concert->orderTickets('janedoe@example.com', 3);

        try {
            $concert->reserveTickets(3, 'johndoe@example.com');
        }catch (NotEnoughTicketsRemainingException) {
            $this->assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Reserving tickets succeeded even though the tickets were already sold');
    }

    /** @test **/
    public function can_reserve_tickets_that_have_already_been_reserved()
    {
        $concert = Concert::factory()->create()->addTickets(5);

        $concert->reserveTickets(3, "janedoe@example.com");

        try {
            $concert->reserveTickets(3, "johndoe@example.com");
        }catch (NotEnoughTicketsRemainingException) {
            $this->assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Reserving tickets succeeded even though the tickets were already reserved');
    }
}





