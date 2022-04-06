<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Concert;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;

class PurchaseTicketTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->concert = Concert::factory()->published()->create();
        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    public function validateData($parameters = []): array
    {
        return array_merge([
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidToken()
        ], $parameters);
    }

    public function postInValidated($data, $valid = null)
    {
        $response = $this->postJson("/concert/{$this->concert->id}/orders", $this->validateData([$data => $valid]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor($data);
    }

    /** @test **/
    public function customer_can_purchase_a_ticket()
    {
        $concert = Concert::factory()->published()->create(['ticket_price' => 2500]);

        $this->postJson("/concert/$concert->id/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidToken()
        ])->assertStatus(201);

        $this->assertEquals(7500, $this->paymentGateway->totalCharges());

        $orders = $concert->orders()->whereEmail('john@example.com')->first();

        $this->assertCount(3, $orders->tickets);

        $this->assertNotNull($orders);
    }

    /** @test **/
    public function customer_can_not_purchase_a_ticket_that_are_not_published()
    {
        $concert = Concert::factory()->unpublished()->create(['ticket_price' => 2500]);

        $this->postJson("/concert/$concert->id/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidToken()
        ])->assertStatus(404);

        $this->assertEquals(0, $this->paymentGateway->totalCharges());

        $orders = $concert->orders()->whereEmail('john@example.com')->first();

        $this->assertNull($orders);
    }

    /** @test **/
    public function order_is_not_created_if_payment_fails()
    {

        $this->postJson("/concert/{$this->concert->id}/orders", $this->validateData(['payment_token' => 'invalid-token']))
            ->assertStatus(422);

        $order = $this->concert->orders()->whereEmail('john@example.com')->first();

        $this->assertNull($order);
    }

    /** @test **/
    public function email_required_to_purchase_a_ticket()
    {
        $this->postInValidated('email');
    }

    /** @test **/
    public function ticket_quantity_required_to_purchase_a_ticket()
    {
        $this->postInValidated('ticket_quantity');
    }

    /** @test **/
    public function ticket_quantity_required_to_and_must_be_more_than_one_purchase_a_ticket()
    {
        $this->postInValidated('ticket_quantity', 0);
    }

    /** @test **/
    public function ticket_quantity_of_type_integer_required_to_purchase_a_ticket()
    {
        $this->postInValidated('ticket_quantity', 'hustler');
    }

    /** @test **/
    public function valid_token_required_to_purchase_a_ticket()
    {
        $this->postInValidated('payment_token');
    }
}
