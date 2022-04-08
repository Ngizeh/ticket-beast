<?php

namespace App\Http\Controllers;

use App\Exceptions\NotEnoughTicketsRemainingException;
use App\Models\Concert;
use App\Billing\PaymentGateway;
use Illuminate\Http\JsonResponse;
use App\Billing\PaymentGatewayException;

class ConcertOrdersController extends Controller
{
    protected $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($id): JsonResponse
    {
        request()->validate([
            'email' => 'required',
            'ticket_quantity' => 'required|integer|min:1',
            'payment_token' => 'required'
        ]);

        $concert = Concert::published()->findOrFail($id);

        try {
            $amount = request('ticket_quantity') * $concert->ticket_price;
            $order = $concert->orderTickets(request('email'), request('ticket_quantity'));
            $this->paymentGateway->charge($amount, request('payment_token'));

            return response()->json($order, 201);

        } catch (PaymentGatewayException $e) {
            $order->cancel();
            return response()->json([], 422);
        } catch (NotEnoughTicketsRemainingException $e) {
            return response()->json([], 422);
        }
    }
}
