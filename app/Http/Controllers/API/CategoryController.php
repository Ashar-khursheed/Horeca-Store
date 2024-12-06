<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Botble\Ecommerce\Models\ProductCategory;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $filterId = $request->get('id'); // Optional ID filter
        $limit = $request->get('limit', 12); // Default limit to 12

        if ($filterId) {
            // Fetch the specific category and its children (parent included)
            $categories = ProductCategory::where('id', $filterId)
                ->orWhere('parent_id', $filterId)
                ->get();
        } else {
            // Fetch all categories if no ID is provided
            $categories = ProductCategory::all();
        }

        // Transform categories into a parent-child structure
        $categoriesTree = $this->buildTree($categories, $filterId, $limit);

        return response()->json($categoriesTree);
    }

    public function show($id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->slug = $category->slug;

        return response()->json([
            'category' => $category,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:ec_product_categories,id',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'image' => 'nullable|string',
            'is_featured' => 'required|boolean',
            'icon' => 'nullable|string',
            'icon_image' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $category = ProductCategory::create($validated);
        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:ec_product_categories,id',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'image' => 'nullable|string',
            'is_featured' => 'required|boolean',
            'icon' => 'nullable|string',
            'icon_image' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $category = ProductCategory::findOrFail($id);
        $category->update($validated);
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }

    private function buildTree($categories, $parentId = 0, $limit = 12)
    {
        $branch = [];
        $count = 0;

        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                // Count products for the category
                $category->productCount = $category->products()->count();

                // Recursively build children
                $children = $this->buildTree($categories, $category->id, $limit);

                if ($children) {
                    $category->children = array_slice($children, 0, $limit);
                } else {
                    $category->children = [];
                }

                $branch[] = $category;

                $count++;
                if ($count >= $limit) {
                    break;
                }
            }
        }

        return $branch;
    }

    public function getProductsByCategory($categoryId)
    {
        $category = ProductCategory::find($categoryId);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $perPage = request()->get('per_page', 10);
        $perPage = is_numeric($perPage) && $perPage > 0 ? (int)$perPage : 10;

        $products = $category->products()->with(['categories', 'brand', 'tags', 'producttypes'])->paginate($perPage);

        $productTypes = $products->getCollection()->flatMap(function ($product) {
            return $product->producttypes;
        })->unique('id');

        $products->getCollection()->transform(function ($product) {
            $totalReviews = $product->reviews->count();
            $avgRating = $totalReviews > 0 ? $product->reviews->avg('star') : null;

            $product->total_reviews = $totalReviews;
            $product->avg_rating = $avgRating;

            if ($product->currency) {
                $product->currency_title = $product->currency->is_prefix_symbol
                    ? $product->currency->title . ' '
                    : $product->price . ' ' . $product->currency->title;
            } else {
                $product->currency_title = $product->price;
            }

            $product->tags = $product->tags;
            $product->producttypes = $product->producttypes;

            return $product;
        });

        return response()->json([
            'category' => $category,
            'products' => $products,
            'producttypes' => $productTypes,
        ]);
    }
}


    // Fetch products by category
    // public function getProductsByCategory($categoryId)
    // {
    //     // Fetch the category by ID
    //     $category = ProductCategory::find($categoryId);

    //     // Check if the category exists
    //     if (!$category) {
    //         return response()->json(['message' => 'Category not found'], 404);
    //     }

    //     // Get the per_page value from the request or default to 10
    //     $perPage = request()->get('per_page', 10); // Default to 10 if not provided

    //     // Validate that per_page is a positive integer
    //     $perPage = is_numeric($perPage) && $perPage > 0 ? (int)$perPage : 10;

    //     // Fetch the products related to this category with pagination
    //     $products = $category->products()->paginate($perPage);

    //     // Enhance product data with reviews and currency
    //     $products->getCollection()->transform(function ($product) {
    //         // Calculate total reviews and average rating
    //         $totalReviews = $product->reviews->count();
    //         $avgRating = $totalReviews > 0 ? $product->reviews->avg('star') : null;

    //         $product->total_reviews = $totalReviews;
    //         $product->avg_rating = $avgRating;

    //         // Handle currency details
    //         if ($product->currency) {
    //             $product->currency_title = $product->currency->is_prefix_symbol
    //                 ? $product->currency->title . ' ' 
    //                 : $product->price . ' ' . $product->currency->title;
    //         } else {
    //             $product->currency_title = $product->price; // Fallback if no currency found
    //         }

    //         return $product;
    //     });

    //     // Return the category along with its paginated and enhanced products
    //     return response()->json([
    //         'category' => $category,
    //         'products' => $products,
    //     ]);
    // }
// }

