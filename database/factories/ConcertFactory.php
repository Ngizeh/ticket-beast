<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConcertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "title" => "Sauti Sol Generation",
            "subtitle" => "and Family of Kenya",
            "date" => Carbon::parse("April 04, 2022 8:00pm"),
            "ticket_price" => 3250,
            "venue" => "Carnivore Stadium",
            "venue_address" => "Along Lang'ata Road",
            "city" => "Nairobi",
            "state" => "Lang'ata",
            "zip" => 12345,
            "additional_information" => "For tickets more details",
        ];
    }

    /**
     * A concert can be viewed if published_at field is not null
     *
     * @return Factory
     */
    public function published(): Factory
    {
        return $this->state([
            'published_at' => Carbon::now()->subWeek(-1)
        ]);
    }

    /**
     * A concert can not be viewed if published_at field is null
     *
     * @return Factory
     */
    public function unpublished(): Factory
    {
        return $this->state([
            'published_at' => null
        ]);
    }
}
