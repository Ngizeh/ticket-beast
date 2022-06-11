<?php

namespace App\Models;

use Illuminate\Support\Collection;

class Reservation
{

    /**
     * @param Collection $tickets
     */
    public function __construct(private readonly Collection $tickets, private readonly string $email){}


    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function email()
    {
        return $this->email;
    }

    public function cancel()
    {
        $this->tickets->each(fn($ticket) => $ticket->release());
    }
}
