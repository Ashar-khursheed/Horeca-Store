<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Botble\Ecommerce\Models\Cart;
use Botble\Ecommerce\Models\SaveForLater;
use Illuminate\Support\Facades\DB;

class SaveForLaterController extends Controller
{
    public function saveForLater(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'product_id' => 'required|exists:ec_products,id', // Updated to match the table name
        ]);

        // Get the logged-in user
        $user = Auth::user();

        // Check if the product exists in the user's cart
        $cartItem = Cart::where('user_id', $user->id)
                        ->where('product_id', $request->product_id)
                        ->first();

        if (!$cartItem) {
            return response()->json([
                'message' => 'Product not found in cart.'
            ], 404);
        }

        // Add the product to the Save for Later table
        SaveForLater::updateOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => $cartItem->quantity,
            ]
        );

        // Remove the product from the cart
        $cartItem->delete();

        return response()->json([
            'message' => 'Product has been moved to Save for Later.',
        ], 200);
    }
}
