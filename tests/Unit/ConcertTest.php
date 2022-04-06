<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use App\Models\Concert;

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
    public function can_order_tickets_from_a_concert()
    {
        $concert = Concert::factory()->create();

        $orders = $concert->orderTickets('janedoe@example.com', 3);

        $this->assertCount(3, $orders->tickets);
        $this->assertEquals('janedoe@example.com', $orders->email);
    }
}

















