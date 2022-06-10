<?php

namespace Tests\Feature;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use App\Models\Concert;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;

class PurchaseTicketTest extends TestCase
{

    public $response;

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
        $this->orderTickets($this->concert, $this->validateData([$data => $valid]));

        $this->assertResponseStatus(422);

        $this->response->assertJsonValidationErrorFor($data);
    }

    public function orderTickets($concert, $data)
    {
        $savedRequest = $this->app['request'];
        $this->response = $this->postJson("/concert/$concert->id/orders", $data);
        $this->app['request'] = $savedRequest;
    }

    public function assertResponseStatus($status)
    {
        $this->response->assertStatus($status);
    }

    public function assertSeeJson($data)
    {
        $this->response->assertExactJson($data);
    }

    /** @test * */
    public function customer_can_purchase_a_ticket()
    {
        $concert = Concert::factory()->published()->create(['ticket_price' => 2500])->addTickets(3);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $this->assertResponseStatus(201);

        $this->assertSeeJson([
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
        ]);

        $this->assertResponseStatus(404);

        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
    }

    /** @test * */
    public function customer_can_not_order_more_tickets_than_remaining()
    {
        $concert = Concert::factory()->published()->create(['ticket_price' => 2500])->addTickets(50);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 52,
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $this->assertResponseStatus(422);

        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());

    }

    /** @test **/
    public function order_is_not_created_if_payment_fails()
    {
        $concert = Concert::factory()->published()->create()->addTickets(50);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 52,
            'payment_token' => 'invalid-token'
        ]);

        $this->assertResponseStatus(422);
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test **/
    public function can_not_purchase_ticket_that_another_customer_is_trying_to_purchase()
    {
        $concert = Concert::factory()->published()->create(['ticket_price' => 1200])->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {

            $this->orderTickets($concert, [
                'email' => 'personA@example.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidToken()
            ]);

            $this->assertResponseStatus(422);
            $this->assertFalse($concert->hasOrderFor('personA@example.com'));
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
        });

         $this->orderTickets($concert, [
            'email' => 'personB@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $this->assertEquals(3600, $this->paymentGateway->totalCharges());
        $this->assertResponseStatus(201);
        $this->assertTrue($concert->hasOrderFor('personB@example.com'));
        $this->assertCount(3, $concert->ordersFor('personB@example.com')->first()->tickets);
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
