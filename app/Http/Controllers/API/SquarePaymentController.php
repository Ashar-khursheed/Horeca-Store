<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SquareService;
use Square\SquareClient;
use Square\Models\CreatePaymentRequest;
use Square\Models\Money;
use Square\Exceptions\ApiException;

class SquarePaymentController extends Controller
{
    private $squareClient;

    public function __construct()
    {
        $this->squareClient = new SquareClient([
            'accessToken' => env('SQUARE_ACCESS_TOKEN'),
            'environment' => env('SQUARE_ENV', 'sandbox'),
        ]);
    }

    public function createPayment(Request $request)
    {
        $request->validate([
            'nonce' => 'required|string',
            'amount' => 'required|numeric|min:0.5', // Amount in dollars
            'currency' => 'required|string|size:3',
        ]);
    
        $nonce = $request->input('nonce');
        $amount = $request->input('amount');
        $currency = strtoupper($request->input('currency'));
    
        try {
            $paymentsApi = $this->squareClient->getPaymentsApi();
    
            // Convert amount from dollars to cents
            $amountInCents = (int) ($amount * 100);
    
            // Create the Money object
            $money = new Money();
            $money->setAmount($amountInCents); // Amount in cents
            $money->setCurrency($currency);
    
            // Create the PaymentRequest with the nested amount_money field
            $paymentRequest = new CreatePaymentRequest(
                $nonce,
                uniqid('payment_'), // Unique payment idempotency key
                [
                    'amount_money' => $money,  // Pass the money object here as a nested field
                ]
            );
    
            // Send the payment request
            $response = $paymentsApi->createPayment($paymentRequest);
    
            if ($response->isSuccess()) {
                return response()->json([
                    'success' => true,
                    'payment' => $response->getResult()->getPayment(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'errors' => $response->getErrors(),
                ], 400);
            }
        } catch (ApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function paymentForm()
{
    return view('payment.form', [
        'square_application_id' => env('SQUARE_APPLICATION_ID'),
    ]);
}

}
