<?php

namespace App\Models;

use Illuminate\Support\Collection;

class Reservation
{

    /**
     * @param Collection $tickets
     */
    public function __construct(private readonly Collection $tickets){}


    public function totalCost()
    {
        return $this->tickets->sum('price');
    }
}
