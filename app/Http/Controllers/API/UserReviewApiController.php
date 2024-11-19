<?php 

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Botble\Ecommerce\Models\Review; // Assuming the review model is located here
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserReviewApiController  extends Controller
{
    /**
     * Get all reviews for the logged-in customer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerReviews()
    {
        $userId = Auth::id(); // Get the authenticated user

        if (!$userId) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        // Fetch reviews for the logged-in customer
        $reviews = Review::where('customer_id', $userId)
            ->with('product') // Eager load product details
            ->get(); // You can also paginate if needed

        // Check if reviews exist
        if ($reviews->isEmpty()) {
            return response()->json(['message' => 'No reviews found for this user.'], 404);
        }

        // Return reviews with product data
        return response()->json($reviews);
    }
}
