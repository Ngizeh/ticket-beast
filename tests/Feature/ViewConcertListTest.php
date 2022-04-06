<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Concert;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewConcertListTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function can_view_published_concerts()
    {

        $concert = Concert::factory()->published()->create([
            "title" => "Mugithi wa Samido",
            "subtitle" => "Joyce wa mama",
            "date" => Carbon::parse("April 04, 2022 8:00pm"),
            "ticket_price" => 3250,
            "venue" => "Karasani Stadium",
            "venue_address" => "Along Thika Road",
            "city" => "Nairobi",
            "state" => "Kasarani",
            "zip" => 12345,
            "additional_information" => "For tickets, call (+254) 735 688 030",
            "published_at" => Carbon::now()
        ]);


        $response = $this->get('/concert/' . $concert->id)->assertStatus(200);


        $response->assertSee($concert->title);
        $response->assertSee($concert->subtitle);
        $response->assertSee("April 04, 2022");
        $response->assertSee('8:00pm');
        $response->assertSee(32.50);
        $response->assertSee($concert->venue);
        $response->assertSee($concert->venue_address);
        $response->assertSee($concert->city);
        $response->assertSee($concert->state);
        $response->assertSee($concert->zip);
        $response->assertSee($concert->additional_information);
    }

    /** @test **/
    public function can_not_view_unpublished_concerts()
    {
        $concert  = Concert::factory()->unpublished()->create();

        $this->get('/concert/'.$concert->id)->assertStatus(404);
    }
}
