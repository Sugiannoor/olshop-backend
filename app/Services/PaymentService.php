<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Request;

class PaymentService
{
    private $merchantCode;
    private $apiKey;
    private $sandbox;

    public function __construct()
    {
        $this->merchantCode = env('DUITKU_MERCHANT_CODE');
        $this->apiKey = env('DUITKU_API_KEY');
        $this->sandbox = env('DUITKU_SANDBOX', true);
    }

    public function createTransaction($order)
    {
        $url = $this->sandbox
            ? 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry'
            : 'https://duitku.com/webapi/api/merchant/inquiry';

        $signature = md5($this->merchantCode . $order['merchand_order_id'] . $order['paymentAmount'] . $this->apiKey);

        $payload = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => $order['paymentAmount'],
            'merchantOrderId' => $order['merchantOrderId'],
            'productDetails' => $order['productDetails'],
            'email' => $order['email'],
            'paymentMethod' => $order['paymentMethod'],
            'customerVaName' => $order['customerVaName'],
            'callbackUrl' => env('APP_URL') . route('payment.callback', [], false),
            'returnUrl' => env('FRONTEND_URL') . '/payment-result',
            'signature' => $signature,
        ];

        $response = Http::post($url, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to create transaction. Error: ' . $response->body());
    }
}
