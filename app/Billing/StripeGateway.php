<?php

namespace App\Billing;

use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;

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
}
