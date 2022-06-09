<?php

namespace App\Models;

use App\Exceptions\NotEnoughTicketsRemainingException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Concert extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = ['date' => 'datetime'];

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function hasOrderFor($customerEmail): bool
    {
        return $this->orders()->whereEmail($customerEmail)->count() > 0;
    }

    public function ordersFor($customerEmail)
    {
        return $this->orders()->whereEmail($customerEmail)->get();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets($email, $ticketQuantity): Model
    {
        $tickets = $this->findTickets($ticketQuantity);

        return $this->createOrder($email, $tickets);
    }

    public function addTickets($tickets): Concert
    {
        foreach (range(1, $tickets) as $i){
            $this->tickets()->create();
        }

        return $this;
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }

    public function getFormattedDateAttribute()
    {
        return  $this->date->format('F d, Y');
    }

    public function getOpensAtAttribute()
    {
        return  $this->date->format('g:ia');
    }

    public function getFormattedTicketPriceAttribute(): string
    {
        return  number_format($this->ticket_price/ 100, 2);
    }


    /**
     * @param $ticketQuantity
     * @return mixed
     */
    public function findTickets($quantity)
    {
        $tickets = $this->tickets()->available()->take($quantity)->get();

        if ($quantity > $tickets->count()) {
            throw new NotEnoughTicketsRemainingException();
        }
        return $tickets;
    }


    /**
     * @param $email
     * @param $tickets
     * @return Model
     */
    public function createOrder($email, $tickets): Model
    {
        $order = $this->orders()->create([
            'email' => $email,
            'amount' => $this->ticket_price * $this->tickets->count()
        ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }
}
