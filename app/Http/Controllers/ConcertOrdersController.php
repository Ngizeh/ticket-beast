<?php

namespace App\Http\Controllers;

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
            $this->paymentGateway->charge($amount, request('payment_token'));
            $concert->orderTickets(request('email'), request('ticket_quantity'));

            return response()->json([], 201);
        } catch (PaymentGatewayException $e){
            return response()->json([], 422);
        }

    }
}
