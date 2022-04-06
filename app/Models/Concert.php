<?php

namespace App\Models;

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

    public function orderTickets($email, $ticketQuantity)
    {
        $order = $this->orders()->create(['email' => $email]);

        foreach (range(1, $ticketQuantity) as $i){
            $order->tickets()->create();
        }

        return $order;
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
