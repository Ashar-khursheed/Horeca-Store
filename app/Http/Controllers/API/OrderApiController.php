<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;  // Import the Str facade

class OrderApiController extends Controller
{
    // Store a new order
// Store a new order
// Assuming this is your OrderController
  public function store(Request $request)
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'shipping_method' => 'required|string',
        'products' => 'required|array',
        'products.*.product_id' => 'required|exists:ec_products,id',
        'products.*.quantity' => 'required|integer|min:1',
        'products.*.price' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // Get the authenticated user's ID
    $user_id = auth()->id(); // This will get the ID of the logged-in user

    if (!$user_id) {
        return response()->json(['error' => 'User not authenticated'], 401);
    }

    // Calculate sub_total and total
    $sub_total = 0;
    foreach ($request->products as $product) {
        $sub_total += $product['price'] * $product['quantity'];
    }

    // Optionally, calculate shipping and taxes
    $shipping_amount = 0; // For now, assume shipping is free
    $tax_amount = $sub_total * 0.1; // Assuming 10% tax rate
    $amount = $sub_total + $shipping_amount + $tax_amount;

    // Create the order
    $order = new Order();
    $order->user_id = $user_id; // Use the authenticated user's ID
    $order->shipping_method = $request->shipping_method;
    $order->amount = $amount;
    $order->sub_total = $sub_total;
    $order->tax_amount = $tax_amount;
    $order->shipping_amount = $shipping_amount;
    $order->status = 'pending';  // Or whatever status you need
    $order->code = '#100000' . rand(1, 9999);  // Generate order code
    $order->token = Str::random(32);  // Generate a unique token for the order
    $order->save();

    // Optionally, store products or other details here as part of the order
    $product_details = [];
    foreach ($request->products as $product) {
        $product_details[] = [
            'product_id' => $product['product_id'],
            'quantity' => $product['quantity'],
            'price' => $product['price'],
        ];
    }
    
    // Optionally, store product details in `description` field
    $order->description = json_encode($product_details);
    $order->save();

    return response()->json($order, 201);  // Return the created order as JSON
}

// Calculate sub_total based on products
private function calculateSubTotal(array $products): float
{
    $sub_total = 0.0;
    foreach ($products as $product) {
        $productModel = Product::find($product['product_id']);
        $sub_total += $productModel->price * $product['quantity'];
    }
    return $sub_total;
}

// Calculate total amount including tax and shipping
private function calculateTotalAmount(array $products, float $sub_total): float
{
    $tax = 0.1 * $sub_total;  // Assuming a 10% tax rate
    $shipping = 0.0;  // You can modify this based on the shipping method
    return $sub_total + $tax + $shipping;
}


    // Fetch all orders for a user
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)->get();
        return response()->json($orders);
    }

    // Get a specific order
    public function show($id)
    {
        $order = Order::findOrFail($id);
        return response()->json($order);
    }

    // Update order status
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json($order);
    }

    // Delete an order
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(null, 204);
    }
}
