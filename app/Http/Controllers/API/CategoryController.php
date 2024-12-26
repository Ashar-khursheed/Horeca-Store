<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    
    
//     public function getSpecificationFilters(Request $request)
// {
//     $categoryId = $request->get('category_id');

//     if (!$categoryId) {
//         return response()->json(['message' => 'Category ID is required'], 400);
//     }

//     // Fetch product IDs for the given category
//     $productIds = DB::table('ec_product_category_product')
//         ->where('category_id', $categoryId)
//         ->pluck('product_id');

//     if ($productIds->isEmpty()) {
//         return response()->json(['message' => 'No products found for this category'], 404);
//     }

//     // Fetch all specifications for these products
//     $specifications = DB::table('specifications')
//         ->whereIn('product_id', $productIds)
//         ->get();

//     if ($specifications->isEmpty()) {
//         return response()->json(['message' => 'No specifications found for this category'], 404);
//     }

//     // Create filters: group unique spec_names and their corresponding values
//     $filters = $specifications->groupBy('spec_name')->map(function ($specs) {
//         return $specs->unique('spec_value')->pluck('spec_value');
//     });

//     // Fetch products grouped by specifications
//     $products = DB::table('ec_products')
//         ->whereIn('id', $productIds)
//         ->get()
//         ->map(function ($product) use ($specifications) {
//             return [
//                 'product_id' => $product->id,
//                 'product_name' => $product->name,
//                 'specifications' => $specifications->where('product_id', $product->id)->map(function ($spec) {
//                     return [
//                         'spec_name' => $spec->spec_name,
//                         'spec_value' => $spec->spec_value,
//                     ];
//                 }),
//             ];
//         });

//     return response()->json([
//         'filters' => $filters,
//         'products' => $products,
//     ]);
// }



// public function getSpecificationFilters(Request $request) 
// {
//     $categoryId = $request->get('category_id');
//     $filters = $request->get('filters', []); // Get filters from the request
//     $perPage = $request->get('per_page', 10); // Define how many items per page (default: 10)

//     if (!$categoryId) {
//         return response()->json(['message' => 'Category ID is required'], 400);
//     }

//     // Fetch product IDs for the given category
//     $productIds = DB::table('ec_product_category_product')
//         ->where('category_id', $categoryId)
//         ->pluck('product_id');

//     if ($productIds->isEmpty()) {
//         return response()->json(['message' => 'No products found for this category'], 404);
//     }

//     // Fetch all specifications for these products
//     $specifications = DB::table('specifications')
//         ->whereIn('product_id', $productIds)
//         ->get();

//     if ($specifications->isEmpty()) {
//         return response()->json(['message' => 'No specifications found for this category'], 404);
//     }

//     // Create filters: group unique spec_names and their corresponding values
//     $availableFilters = $specifications->groupBy('spec_name')->map(function ($specs) {
//         return $specs->unique('spec_value')->pluck('spec_value');
//     });

//     // Filter products based on the selected filter criteria
//     $filteredProductIds = collect($productIds);

//     // Apply user filters
//     foreach ($filters as $filter) {
//         $filteredProductIds = DB::table('specifications')
//             ->whereIn('product_id', $filteredProductIds)  // Limit to previously filtered products
//             ->where('spec_name', $filter['spec_name'])
//             ->where('spec_value', $filter['spec_value'])
//             ->pluck('product_id');
//     }

//     if ($filteredProductIds->isEmpty()) {
//         return response()->json(['message' => 'No products match the selected filters'], 404);
//     }

//     // Fetch the filtered products with pagination
//     $products = DB::table('ec_products')
//         ->whereIn('id', $filteredProductIds)
//         ->paginate($perPage) // Use paginate for pagination
//         ->map(function ($product) use ($specifications) {
//             // Initialize total reviews and average rating
//             $totalReviews = DB::table('ec_reviews')->where('product_id', $product->id)->count();
//             $avgRating = $totalReviews > 0 ? DB::table('ec_reviews')->where('product_id', $product->id)->avg('star') : null;

//             // Set product properties
//             $product->total_reviews = $totalReviews;
//             $product->avg_rating = $avgRating;

//             // Handle currency
//             $currency = DB::table('ec_currencies')->where('id', $product->currency_id)->first(); // Assuming you have currency_id in the product table
//             if ($currency) {
//                 $product->currency_title = $currency->is_prefix_symbol
//                     ? $currency->title . ' ' . $product->price
//                     : $product->price . ' ' . $currency->title;
//             } else {
//                 $product->currency_title = $product->price;
//             }

//             // Specifications
//             $product->specifications = $specifications->where('product_id', $product->id)->map(function ($spec) {
//                 return [
//                     'spec_name' => $spec->spec_name,
//                     'spec_value' => $spec->spec_value,
//                 ];
//             });

//             return $product;
//         });

//     return response()->json([
//         'filters' => $availableFilters,
//         'products' => $products,
//     ]);
// }


public function getSpecificationFilters(Request $request)
{
    $categoryId = $request->get('category_id');
    $filters = $request->get('filters', []); // Get filters from the request
    $perPage = $request->get('per_page', 10); // Default items per page

    if (!$categoryId) {
        return response()->json(['message' => 'Category ID is required'], 400);
    }

    // Fetch product IDs for the given category
    $productIds = DB::table('ec_product_category_product')
        ->where('category_id', $categoryId)
        ->pluck('product_id');

    if ($productIds->isEmpty()) {
        return response()->json(['message' => 'No products found for this category'], 404);
    }

    // Fetch all specifications for these products
    $specifications = DB::table('specifications')
        ->whereIn('product_id', $productIds)
        ->get();

    if ($specifications->isEmpty()) {
        return response()->json(['message' => 'No specifications found for this category'], 404);
    }

    // Create filters: group unique spec_names and generate ranges dynamically
    $availableFilters = $specifications->groupBy('spec_name')->map(function ($specs, $specName) {
        $numericValues = $specs->pluck('spec_value')->filter(fn($value) => is_numeric($value))->unique()->sort()->values();
        $nonNumericValues = $specs->pluck('spec_value')->filter(fn($value) => !is_numeric($value))->unique();

        $ranges = [];
        if ($numericValues->count() > 1) {
            $minValue = $numericValues->first();
            $maxValue = $numericValues->last();

            $interval = ceil(($maxValue - $minValue) / 4); // Divide into 4 equal ranges

            for ($i = 0; $i < 4; $i++) {
                $start = $minValue + $i * $interval;
                $end = min($minValue + ($i + 1) * $interval, $maxValue);

                $ranges[] = [
                    'min' => (int) $start,
                    'max' => (int) $end,
                ];
            }
        }

        return [
            'ranges' => $ranges,
            'non_numeric_values' => $nonNumericValues,
        ];
    });

    // Filter products based on the selected filter criteria
    $filteredProductIds = collect($productIds);

    foreach ($filters as $filter) {
        $filteredProductIds = DB::table('specifications')
            ->whereIn('product_id', $filteredProductIds)
            ->where('spec_name', $filter['spec_name'])
            ->whereBetween('spec_value', [$filter['min'], $filter['max']])
            ->pluck('product_id');
    }

    if ($filteredProductIds->isEmpty()) {
        return response()->json(['message' => 'No products match the selected filters'], 404);
    }

    // Fetch the filtered products with the required fields
    $products = DB::table('ec_products')
        ->select([
            'id', 'name', 'images', 'sku', 'price', 'sale_price', 'refund', 
            'delivery_days', 'currency_id',
        ])
        ->whereIn('id', $filteredProductIds)
        ->paginate($perPage);

    $products->transform(function ($product) use ($specifications) {
        // Add currency title
        $currency = DB::table('ec_currencies')->where('id', $product->currency_id)->first();
        $product->currency_title = $currency 
            ? ($currency->is_prefix_symbol 
                ? $currency->title . ' ' . $product->price 
                : $product->price . ' ' . $currency->title) 
            : $product->price;

        // Calculate average rating
        $totalReviews = DB::table('ec_reviews')->where('product_id', $product->id)->count();
        $product->avg_rating = $totalReviews > 0 
            ? DB::table('ec_reviews')->where('product_id', $product->id)->avg('star') 
            : null;

        // Map specifications
        $product->specifications = $specifications->where('product_id', $product->id)->map(function ($spec) {
            return [
                'spec_name' => $spec->spec_name,
                'spec_value' => $spec->spec_value,
            ];
        });

        // Format images
        $imagePaths = $product->images ? json_decode($product->images, true) : [];
        $product->images = array_map(fn($imagePath) => asset('storage/' . $imagePath), $imagePaths);

        return $product;
    });

    return response()->json([
        'filters' => $availableFilters,
        'products' => $products,
    ]);
}




// public function getSpecificationFilters(Request $request)
// {
//     $categoryId = $request->get('category_id');
//     $filters = $request->get('filters', []); // Get filters from the request

//     if (!$categoryId) {
//         return response()->json(['message' => 'Category ID is required'], 400);
//     }

//     // Fetch product IDs for the given category
//     $productIds = DB::table('ec_product_category_product')
//         ->where('category_id', $categoryId)
//         ->pluck('product_id');

//     if ($productIds->isEmpty()) {
//         return response()->json(['message' => 'No products found for this category'], 404);
//     }

//     // Fetch all specifications for these products
//     $specifications = DB::table('specifications')
//         ->whereIn('product_id', $productIds)
//         ->get();

//     if ($specifications->isEmpty()) {
//         return response()->json(['message' => 'No specifications found for this category'], 404);
//     }

//     // Create filters: group unique spec_names and their corresponding values
//     $availableFilters = $specifications->groupBy('spec_name')->map(function ($specs) {
//         return $specs->unique('spec_value')->pluck('spec_value');
//     });

//     // Filter products based on the selected filter criteria
//     $filteredProductIds = collect($productIds);

//     // Apply user filters
//     foreach ($filters as $filter) {
//         $filteredProductIds = DB::table('specifications')
//             ->whereIn('product_id', $filteredProductIds)  // Limit to previously filtered products
//             ->where('spec_name', $filter['spec_name'])
//             ->where('spec_value', $filter['spec_value'])
//             ->pluck('product_id');
//     }

//     if ($filteredProductIds->isEmpty()) {
//         return response()->json(['message' => 'No products match the selected filters'], 404);
//     }

//     // Fetch the filtered products
//     $products = DB::table('ec_products')
//         ->whereIn('id', $filteredProductIds)
//         ->get()
//         ->map(function ($product) use ($specifications) {
//             return [
//                 'product_id' => $product->id,
//                 'product_name' => $product->name,
//                   'product_name' => $product->name,
//                     'product_name' => $product->name,
//                       'product_name' => $product->name,
//                         'product_name' => $product->name,
                
//                 'specifications' => $specifications->where('product_id', $product->id)->map(function ($spec) {
//                     return [
//                         'spec_name' => $spec->spec_name,
//                         'spec_value' => $spec->spec_value,
//                     ];
//                 }),
//             ];
//         });

//     return response()->json([
//         'filters' => $availableFilters,
//         'products' => $products,
//     ]);
// }

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

