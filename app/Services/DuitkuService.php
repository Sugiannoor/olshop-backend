<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DuitkuService
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

    public function getPaymentMethods($amount)
    {
        $datetime = now()->format('Y-m-d H:i:s');
        $signature = hash('sha256', $this->merchantCode . $amount . $datetime . $this->apiKey);

        $payload = [
            'merchantcode' => $this->merchantCode,
            'amount' => $amount,
            'datetime' => $datetime,
            'signature' => $signature,
        ];

        $url = $this->sandbox
            ? 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod'
            : 'https://passport.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to fetch payment methods: ' . $response->body());
    }

    public function checkTransactionStatus(string $merchantOrderId): array
    {
        $signature = md5($this->merchantCode . $merchantOrderId . $this->apiKey);

        $url = $this->sandbox
            ? 'https://sandbox.duitku.com/webapi/api/merchant/transactionStatus'
            : 'https://passport.duitku.com/webapi/api/merchant/transactionStatus';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'merchantCode' => $this->merchantCode,
            'merchantOrderId' => $merchantOrderId,
            'signature' => $signature,
        ]);

        return $response->json();
    }
}
