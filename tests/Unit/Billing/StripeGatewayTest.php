<?php

namespace Tests\Unit\Billing;

use Tests\TestCase;
use App\Billing\StripeGateway;
use App\Billing\PaymentGatewayException;


/**
 * @group Integration
 */
class StripeGatewayTest extends TestCase
{

    use PaymentGatewayContractTest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentGateway = new StripeGateway(config('services.stripe.secret'));
    }
}
