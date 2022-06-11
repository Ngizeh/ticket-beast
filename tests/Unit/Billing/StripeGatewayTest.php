<?php

namespace Tests\Unit\Billing;

use App\Billing\StripeGateway;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\Token;
use Tests\TestCase;

class StripeGatewayTest extends TestCase
{

    /**
     * @throws ApiErrorException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->lastCharge = $this->lastCharge();
        $this->apiKey =  config('services.stripe.secret');
    }

    /** @test *
     * @throws ApiErrorException
     */
    public function it_can_make_a_charge_with_valid_token_from_stripe()
    {
        $paymentGateway = new StripeGateway($this->apiKey);

        $paymentGateway->charge(2500, $this->validToken());

        $this->assertEquals(2500, $this->lastCharge()->amount);

        $this->assertCount(1, $this->newCharges());
    }

    /**
     * @return mixed
     * @throws ApiErrorException
     */
    public function lastCharge(): mixed
    {
        return Charge::all(
            ['limit' => 1],
            ["api_key" => config('services.stripe.secret')]
        )['data'][0];
    }

    /**
     * @return mixed
     * @throws ApiErrorException
     */
    public function newCharges(): mixed
    {
        return Charge::all(
            [
                'limit' => 1,
                'ending_before' => $this->lastCharge->id
            ],
            ["api_key" => config('services.stripe.secret')]
        )['data'];
    }

    /**
     * @return string
     * @throws ApiErrorException
     */
    public function validToken(): string
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
}
