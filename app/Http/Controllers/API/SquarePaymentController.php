<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SquareService;

class SquarePaymentController extends Controller
{
    protected $squareService;

    public function __construct(SquareService $squareService)
    {
        $this->squareService = $squareService;
    }

    public function processPayment($nonce, $amount)
    {
        $money = new \Square\Models\Money();
        $money->setAmount((int)($amount * 100)); // Convert to cents
        $money->setCurrency('USD');
    
        $paymentRequest = new \Square\Models\CreatePaymentRequest($nonce, uniqid(), $money);
    
        try {
            $paymentsApi = $this->client->getPaymentsApi();
            $response = $paymentsApi->createPayment($paymentRequest);
    
            if ($response->isSuccess()) {
                return [
                    'success' => true,
                    'data' => $response->getResult(),
                ];
            }
    
            return [
                'success' => false,
                'errors' => $response->getErrors(),
            ];
        } catch (\Square\Exceptions\ApiException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    
    
}
