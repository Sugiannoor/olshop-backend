<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct() {}

    public function checkout(Request $request, OrderService $orderService, PaymentService $paymentService)
    {
        $user = Auth::user();
        $request->validate(
            [
                'address' => 'required|string|max:255',
                'payment_method' => 'required|string',
            ],
            [
                'address.required' => 'Alamat pengiriman harus diisi.',
                'payment_method.required' => 'Metode pembayaran harus diisi.',
            ]
        );
        $order = $orderService->createOrder($user, $request->address, $request->paymentMethod, $request->qr_string, $request->va_number, $request->reference);
        $transaction = $paymentService->createTransaction($order);
        return response()->json([
            'code' => 201,
            'merchant_order_id' => $order->merchant_order_id,
            'message' => 'Order created successfully',
            'paymentUrl' => $transaction['paymentUrl'],
            'orderId' => $order->id,
            'status' => 'success',
        ], 201);
    }
}
