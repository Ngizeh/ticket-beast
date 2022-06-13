<?php

namespace App\Billing;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;
use Stripe\Token;

class StripeGateway implements PaymentGateway
{
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge($amount, $token)
    {
        try {
            Charge::create([
                "amount" => $amount,
                "source" => $token,
                "currency" => "usd"
            ], ["api_key" => $this->apiKey]);
        }catch (InvalidRequestException) {
            throw new PaymentGatewayException();
        }
    }

    public function newChargeDuring($callback)
    {
        $newCharge = $this->lastCharge();

        $callback($this);

        return $this->newCharges($newCharge)->pluck('amount');
    }

    public function getValidToken(): string
    {
        return Token::create([
            "card" => [
                "number" => "4242424242424242",
                "exp_month" => 1,
                "exp_year" => date('Y') + 1,
                "cvc" => "123"
            ]
        ], ["api_key" => $this->apiKey])->id;
    }

    public function lastCharge()
    {
        return Arr::first(Charge::all(
            ['limit' => 1],
            ["api_key" => $this->apiKey]
        )['data']);
    }


    public function newCharges($charge = null): Collection
    {
        $charge = Charge::all(
            [
                'ending_before' => $charge ? $charge->id : null
            ],
            ["api_key" => config('services.stripe.secret')]
        )['data'];

        return collect($charge);
    }
}
