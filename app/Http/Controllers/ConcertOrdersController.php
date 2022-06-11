<?php

namespace App\Http\Controllers;

use App\Exceptions\NotEnoughTicketsRemainingException;
use App\Models\Concert;
use App\Billing\PaymentGateway;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use App\Billing\PaymentGatewayException;

class ConcertOrdersController extends Controller
{
    protected PaymentGateway $paymentGateway;

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

            $reservation = $concert->reserveTickets(request('ticket_quantity'), request('email'));

            $order = $reservation->complete($this->paymentGateway, request('payment_token'));

            return response()->json($order, 201);

        } catch (PaymentGatewayException){
            $reservation->cancel();
            return response()->json([], 422);

        }catch(NotEnoughTicketsRemainingException) {
            return response()->json([], 422);
        }
    }
}
