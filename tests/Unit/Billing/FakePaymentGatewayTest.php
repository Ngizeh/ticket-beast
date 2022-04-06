<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Billing\PaymentGatewayException;
use PHPUnit\Framework\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    /** @test **/
    public function payment_gateway_charges_with_a_valid_token()
    {
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge(2500, $paymentGateway->getValidToken());

        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }

    /** @test **/
    public function payment_gateway_fails_with_invalid_payment_token()
    {
        $this->expectException(PaymentGatewayException::class);

        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge(2500, 'invalid-payment-token');

    }
}
