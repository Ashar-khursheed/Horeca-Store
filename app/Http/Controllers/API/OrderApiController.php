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
//   public function store(Request $request)
// {
//     // Validate the incoming request data
//     $validator = Validator::make($request->all(), [
//         'shipping_method' => 'required|string',
//         'products' => 'required|array',
//         'products.*.product_id' => 'required|exists:ec_products,id',
//         'products.*.quantity' => 'required|integer|min:1',
//         'products.*.price' => 'required|numeric|min:0',
//     ]);

//     if ($validator->fails()) {
//         return response()->json($validator->errors(), 422);
//     }

//     // Get the authenticated user's ID
//     $user_id = auth()->id(); // This will get the ID of the logged-in user

//     if (!$user_id) {
//         return response()->json(['error' => 'User not authenticated'], 401);
//     }

//     // Calculate sub_total and total
//     $sub_total = 0;
//     foreach ($request->products as $product) {
//         $sub_total += $product['price'] * $product['quantity'];
//     }

//     // Optionally, calculate shipping and taxes
//     $shipping_amount = 0; // For now, assume shipping is free
//     $tax_amount = $sub_total * 0.1; // Assuming 10% tax rate
//     $amount = $sub_total + $shipping_amount + $tax_amount;

//     // Create the order
//     $order = new Order();
//     $order->user_id = $user_id; // Use the authenticated user's ID
//     $order->shipping_method = $request->shipping_method;
//     $order->amount = $amount;
//     $order->sub_total = $sub_total;
//     $order->tax_amount = $tax_amount;
//     $order->shipping_amount = $shipping_amount;
//     $order->status = 'pending';  // Or whatever status you need
//     $order->code = '#100000' . rand(1, 9999);  // Generate order code
//     $order->token = Str::random(32);  // Generate a unique token for the order
//     $order->save();

//     // Optionally, store products or other details here as part of the order
//     $product_details = [];
//     foreach ($request->products as $product) {
//         $product_details[] = [
//             'product_id' => $product['product_id'],
//             'quantity' => $product['quantity'],
//             'price' => $product['price'],
//         ];
//     }
    
//     // Optionally, store product details in `description` field
//     $order->description = json_encode($product_details);
//     $order->save();

//     return response()->json($order, 201);  // Return the created order as JSON
// }

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
    $order->shipping_method = json_encode([
        'value' => $request->shipping_method,
        'label' => 'Default' // You can modify this if you have dynamic shipping methods
    ]);
    $order->amount = $amount;
    $order->sub_total = $sub_total;
    $order->tax_amount = $tax_amount;
    $order->shipping_amount = $shipping_amount;
    $order->status = json_encode([
        'value' => 'pending',
        'label' => 'Pending'
    ]);  // Setting status as pending
    $order->code = '#100000' . rand(1, 9999);  // Generate order code
    $order->token = Str::random(32);  // Generate a unique token for the order
    $order->is_confirmed = 0; // Assuming the order is not confirmed yet
    $order->is_finished = 1;  // Assuming the order is finished for now
    $order->cancellation_reason = null; // Assuming no cancellation reason
    $order->cancellation_reason_description = null; // Assuming no cancellation description
    $order->completed_at = null; // Assuming the order is not completed yet
    $order->store_id = 7; // Store ID should be set accordingly
    $order->created_at = now();
    $order->updated_at = now();
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

    // Return the created order as JSON, including all the necessary fields
    return response()->json([
        'id' => $order->id,
        'code' => $order->code,
        'user_id' => $order->user_id,
        'shipping_option' => '3', // This can be dynamic based on the shipping method
        'shipping_method' => json_decode($order->shipping_method),
        'status' => json_decode($order->status),
        'amount' => $order->amount,
        'tax_amount' => $order->tax_amount,
        'shipping_amount' => $order->shipping_amount,
        'description' => $order->description,
        'coupon_code' => null, // You can adjust this if coupon codes are being used
        'discount_amount' => 0.00, // Assuming no discount is applied
        'sub_total' => $order->sub_total,
        'is_confirmed' => $order->is_confirmed,
        'discount_description' => null, // Assuming no discount description
        'is_finished' => $order->is_finished,
        'cancellation_reason' => $order->cancellation_reason,
        'cancellation_reason_description' => $order->cancellation_reason_description,
        'completed_at' => $order->completed_at,
        'token' => $order->token,
        'payment_id' => 5, // You can adjust this based on the actual payment method
        'created_at' => $order->created_at,
        'updated_at' => $order->updated_at,
        'proof_file' => null, // Assuming no proof file is uploaded
        'store_id' => $order->store_id,
        'products' => json_decode($order->description) // Return the product details
    ], 201);  // Return the full order data as JSON
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
    // public function index(Request $request)
    // {
    //     $orders = Order::where('user_id', $request->user()->id)->get();
    //     return response()->json($orders);
    // }
public function index(Request $request)
{
    // Retrieve the orders for the logged-in user, including related data
    $orders = Order::where('user_id', $request->user()->id)->get();

    // If no orders are found, return a message
    if ($orders->isEmpty()) {
        return response()->json(['message' => 'No orders found'], 404);
    }

    // Iterate through each order and extract product details from the 'description' field
    $orders->transform(function ($order) {
        // Check if the 'description' field is not empty or null
        if ($order->description) {
            // Decode the 'description' field (which contains JSON data)
            $productDetails = json_decode($order->description, true);

            // Ensure the decoded JSON is an array
            if (is_array($productDetails)) {
                // Initialize an array to store the product details
                $products = [];

                // Loop through each product in the 'description' field and retrieve product data
                foreach ($productDetails as $item) {
                    // Fetch the product details from the 'ec_products' table based on the 'product_id'
                    $product = Product::find($item['product_id']);

                    // If the product exists, add the necessary details
                    if ($product) {
                        $products[] = [
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'price' => $item['price'],
                             'sale_price' => $product->sale_price,
                            'quantity' => $item['quantity'],
                            'description' => $product->description,  // Include other details as needed
                            'images' => $product-> images
                        ];
                    }
                }

                // Attach the product details to the order as a custom attribute
                $order->setAttribute('products', $products);
            } else {
                // If the description is not a valid array, set products to an empty array
                $order->setAttribute('products', []);
            }
        } else {
            // If description is null or empty, set products to an empty array
            $order->setAttribute('products', []);
        }

        // Return the updated order with the product details
        return $order;
    });

    // Return the orders with the product details as a JSON response
    return response()->json($orders);
}


    // Get a specific order
    public function show($id)
    {
        $order = Order::findOrFail($id);
        return response()->json($order);
    }
// Get a specific order with products
// public function show($id)
// {
//     // Get the order by ID
//     $order = Order::findOrFail($id);

//     // Decode the description field from JSON
//     $productData = json_decode($order->description, true);

//     // Initialize an array to store the product details with quantity and price
//     $productsWithDetails = [];

//     // Loop through the product data
//     foreach ($productData as $item) {
//         // Fetch the product details from the ec_products table using the product_id
//         $product = Product::find($item['product_id']);

//         if ($product) {
//             // Add the product details along with quantity and price to the array
//             $productsWithDetails[] = [
//                 'product_id' => $item['product_id'],
//                 'quantity' => $item['quantity'],
//                 'price' => $item['price'],
//                 'total_price' => $item['quantity'] * $item['price'], // Calculate total price
//                 'product_details' => $product // Add product details (name, description, etc.)
//             ];
//         }
//     }

//     // Optionally, you can attach this to the order object or return it separately
//     return response()->json([
//         'order' => $order,
//         'products' => $productsWithDetails
//     ]);
// }

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
