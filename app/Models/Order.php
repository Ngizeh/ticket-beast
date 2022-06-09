<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use phpDocumentor\Reflection\Types\This;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function concert(): BelongsToMany
    {
        return $this->belongsToMany(Concert::class, 'tickets');
    }

    public function cancel()
    {
        foreach ($this->tickets as $ticket){
            $ticket->release();
        }

        $this->delete();
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'ticket_quantity' => $this->tickets()->count(),
            'amount' => $this->amount,
        ];
    }
}
