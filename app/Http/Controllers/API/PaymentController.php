<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DuitkuService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Str;

class PaymentController extends Controller
{
    private $paymentService;
    private $duitkuService;

    public function __construct(PaymentService $paymentService, DuitkuService $duitkuService)
    {
        $this->paymentService = $paymentService;
        $this->duitkuService = $duitkuService;
    }

    public function create(Request $request)
    {
        $order = [
            'merchantOrderId' => Str::uuid(),
            'paymentAmount' => $request->input('amount'),
            'productDetails' => 'Pembayaran Produk Toko',
            'email' => $request->input('email'),
            'paymentMethod' => $request->input('payment_method'),
            'customerVaName' => $request->input('name'),
        ];

        try {
            $response = $this->paymentService->createTransaction($order);

            return response()->json([
                'code' => 200,
                'message' => 'Transaksi berhasil dibuat',
                'paymentUrl' => $response['paymentUrl'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function callback(Request $request)
    {
        $merchantCode = $request->input('merchantCode');
        $amount = $request->input('amount');
        $merchantOrderId = $request->input('merchantOrderId');
        $resultCode = $request->input('resultCode');
        $paymentCode = $request->input('paymentCode');
        $reference = $request->input('reference');
        $signature = $request->input('signature');
        $apiKey = env('DUITKU_API_KEY');

        $calculatedSignature = md5($merchantCode . $amount . $merchantOrderId . $apiKey);
        if ($signature !== $calculatedSignature) {
            return response()->json([
                'message' => 'Invalid signature',
                'status' => 'error',
                'code' => 403,
            ], 403);
        }
        $order = Order::where('merchant_order_id', $merchantOrderId)->first();
        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
                'status' => 'error',
                'code' => 404,
            ], 404);
        }
        if ($order->total_price != $amount) {
            return response()->json([
                'message' => 'Amount mismatch',
                'status' => 'error',
                'code' => 400,
            ], 400);
        }

        try {
            if ($resultCode == '00') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'completed',
                    'payment_reference' => $reference,
                    'payment_method' => $paymentCode,
                ]);
            } elseif ($resultCode == '01') {
                $order->update([
                    'payment_status' => 'failed',
                    'status' => 'canceled',
                    'payment_reference' => $reference,
                ]);
            } else {
                $order->update([
                    'payment_status' => 'pending',
                    'status' => 'pending',
                    'payment_reference' => $reference,
                ]);
            }

            return response()->json([
                'message' => 'Callback processed successfully',
                'status' => 'success',
                'code' => 200,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process callback',
                'error' => $e->getMessage(),
                'status' => 'error',
                'code' => 500,
            ], 500);
        }
    }

    public function redirect(Request $request)
    {
        $merchantOrderId = $request->input('merchantOrderId');
        $resultCode = $request->input('resultCode');
        $reference = $request->input('reference');
        return redirect()->to(env('FRONTEND_URL') . "/payment-result?merchantOrderId={$merchantOrderId}&resultCode={$resultCode}&reference={$reference}");
    }

    public function getPaymentMethods(Request $request)
    {
        $amount = $request->input('amount');
        if (!$amount || !is_numeric($amount)) {
            return response()->json([
                'message' => 'Invalid amount provided.',
                'status' => 'error',
                'code' => 400,
            ], 400);
        }

        try {
            $paymentMethods = $this->duitkuService->getPaymentMethods($amount);
            return response()->json([
                'message' => 'Payment methods retrieved successfully.',
                'data' => $paymentMethods,
                'status' => 'success',
                'code' => 200,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
                'code' => 500,
            ], 500);
        }
    }
}
