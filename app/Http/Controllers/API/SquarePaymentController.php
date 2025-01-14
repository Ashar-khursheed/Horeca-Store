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

    public function processPayment($nonce, $amount, $currency = 'USD')
{
    $paymentsApi = $this->client->getPaymentsApi();

    // Create Money object with amount and currency
    $money = new \Square\Models\Money();
    $money->setAmount((int)($amount * 100)); // Convert amount to cents
    $money->setCurrency($currency);

    // Create Payment Request
    $createPaymentRequest = new \Square\Models\CreatePaymentRequest($nonce, uniqid(), $money);

    try {
        $response = $paymentsApi->createPayment($createPaymentRequest);

        if ($response->isSuccess()) {
            return $response->getResult();
        } else {
            return $response->getErrors();
        }
    } catch (\Square\Exceptions\ApiException $e) {
        return ['error' => $e->getMessage()];
    }
}

}
