<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentGatewayException;

trait PaymentGatewayContractTest
{
    /** @test * */
    public function payment_gateway_charges_with_a_valid_token()
    {
        $paymentGateway = $this->paymentGateway;

        $newCharge = $paymentGateway->newChargeDuring(function ($paymentGateway) {
            $paymentGateway->charge(2500, $paymentGateway->getValidToken());
        });

        $this->assertEquals(2500, $newCharge->sum());

        $this->assertCount(1, $newCharge);
    }


    /** @test **/
    public function payment_gateway_fails_with_invalid_payment_token()
    {
        $this->expectException(PaymentGatewayException::class);

        $paymentGateway = $this->paymentGateway;

        $newCharges = $paymentGateway->newChargeDuring(function ($paymentGateway){
            $paymentGateway->charge(2500, 'invalid-payment-token');
        });

        $this->assertCount(0, $newCharges->sum());

    }

    /** @test **/
    public function charges_with_the_new_charge_callback()
    {
        $paymentGateway = $this->paymentGateway;

        $paymentGateway->charge(1000, $paymentGateway->getValidToken());
        $paymentGateway->charge(2000, $paymentGateway->getValidToken());

        $newCharges = $paymentGateway->newChargeDuring(function ($paymentGateway) {
            $paymentGateway->charge(2000, $paymentGateway->getValidToken());
            $paymentGateway->charge(3000, $paymentGateway->getValidToken());
        });

        $this->assertCount(2, $newCharges);

        $this->assertEquals([3000, 2000], $newCharges->all());
    }

}
