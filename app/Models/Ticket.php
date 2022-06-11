<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopeAvailable($query)
    {
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    public function concert(): BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }

    public function reserve(): bool
    {
        return $this->update(['reserved_at' => now()]);
    }

    public function release()
    {
        $this->update(['reserved_at' => null]);
    }

    public function getPriceAttribute()
    {
        return $this->concert->ticket_price;
    }
}
