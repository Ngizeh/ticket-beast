<?php

namespace App\Billing;

class FakePaymentGateway implements PaymentGateway
{
    private $charges;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidToken(): string
    {
        return "valid_token";
    }

    public function charge($amount, $token)
    {
        if($token !== $this->getValidToken()){
            throw new PaymentGatewayException;
        }
       $this->charges[] = $amount;
    }

    public function totalCharges()
    {
        return $this->charges->sum();
    }

}
