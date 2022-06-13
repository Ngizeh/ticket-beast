<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Billing\PaymentGatewayException;
use PHPUnit\Framework\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway();
    }


    /** @test **/
    public function before_the_first_charge_hook_should_fail_to_avoid_the_intercept()
    {
        $paymentGateway = $this->paymentGateway;

        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function($paymentGateway) use (&$timesCallbackRan) {
            $timesCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidToken());
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidToken());

        $this->assertEquals(1, $timesCallbackRan);

        $this->assertEquals(5000, $paymentGateway->totalCharges());

    }
}
