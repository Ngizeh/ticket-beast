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

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets($email, $ticketQuantity)
    {
        $tickets = $this->tickets()->available()->take($ticketQuantity)->get();

        if($ticketQuantity > $tickets->count()){
            throw new NotEnoughTicketsRemainingException();
        }

        $order = $this->orders()->create(['email' => $email]);

        foreach ($tickets as $ticket){
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public function addTickets($tickets)
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

    public function getFormattedTicketPriceAttribute()
    {
        return  number_format($this->ticket_price/ 100, 2);
    }
}
