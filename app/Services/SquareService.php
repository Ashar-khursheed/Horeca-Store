<?php

namespace App\Services;
require 'vendor/autoload.php';
use Square\SquareClient;
use Square\Exceptions\ApiException;
use Square\Models\CreatePaymentRequest;

class SquareService
{
    protected $client;

    public function __construct()
    {
        $this->client = new SquareClient([
            'accessToken' => env('SQUARE_ACCESS_TOKEN'),
            'environment' => env('SQUARE_ENV', 'sandbox'),
        ]);
    }

    public function processPayment($nonce, $amount, $currency = 'USD')
    {
        $paymentsApi = $this->client->getPaymentsApi();

        $money = new \Square\Models\Money();
        $money->setAmount($amount * 100); // Amount in cents
        $money->setCurrency($currency);

        $createPaymentRequest = new CreatePaymentRequest($nonce, uniqid(), $money);

        try {
            $response = $paymentsApi->createPayment($createPaymentRequest);

            if ($response->isSuccess()) {
                return $response->getResult();
            } else {
                return $response->getErrors();
            }
        } catch (ApiException $e) {
            return $e->getMessage();
        }
    }
}
