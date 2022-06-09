<?php

namespace Tests\Feature;

use Illuminate\Testing\TestResponse;
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
        $response = $this->orderTickets($this->concert, $this->validateData([$data => $valid]));

        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor($data);
    }

    public function orderTickets($concert, $data): TestResponse
    {
        return $this->postJson("/concert/$concert->id/orders", $data);
    }

    /** @test * */
    public function customer_can_purchase_a_ticket()
    {
        $concert = Concert::factory()->published()->create(['ticket_price' => 2500])->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $response->assertStatus(201);

        $response->assertExactJson([
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'amount' => 7500,
        ]);

        $this->assertEquals(7500, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('john@example.com'));
        $this->assertCount(3, $concert->ordersFor('john@example.com')->first()->tickets);
    }

    /** @test * */
    public function customer_can_not_purchase_a_ticket_that_are_not_published()
    {
        $concert = Concert::factory()->unpublished()->create(['ticket_price' => 2500])->addTickets(3);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidToken()
        ])->assertStatus(404);

        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
    }

    /** @test * */
    public function customer_can_not_order_more_tickets_than_remaining()
    {
        $concert = Concert::factory()->published()->create(['ticket_price' => 2500])->addTickets(50);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 52,
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());

    }

    /** @test **/
    public function order_is_not_created_if_payment_fails()
    {
        $concert = Concert::factory()->published()->create()->addTickets(50);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 52,
            'payment_token' => 'invalid-token'
        ]);

        $response->assertStatus(422);
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test * */
    public function email_required_to_purchase_a_ticket()
    {
        $this->postInValidated('email');
    }

    /** @test * */
    public function ticket_quantity_required_to_purchase_a_ticket()
    {
        $this->postInValidated('ticket_quantity');
    }


    /** @test * */
    public function ticket_quantity_required_to_and_must_be_more_than_one_purchase_a_ticket()
    {
        $this->postInValidated('ticket_quantity', 0);
    }

    /** @test * */
    public function ticket_quantity_of_type_integer_required_to_purchase_a_ticket()
    {
        $this->postInValidated('ticket_quantity', 'hustler');
    }

    /** @test * */
    public function valid_token_required_to_purchase_a_ticket()
    {
        $this->postInValidated('payment_token');
    }
}
