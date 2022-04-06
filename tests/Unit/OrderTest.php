<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class OrderTest extends TestCase
{
    /** @test **/
    public function can_have_many_tickets()
    {
        $concert = Concert::factory()->create();

        $order = Order::factory()->create(['concert_id' => $concert]);

        $this->assertInstanceOf(Collection::class, $order->tickets);
    }
}
