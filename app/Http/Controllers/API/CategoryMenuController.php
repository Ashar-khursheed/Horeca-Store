<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Botble\Ecommerce\Models\ProductCategory;
class CategoryMenuController extends Controller
{
    /**
     * Get category names, slugs, IDs, and their children.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategoriesWithChildren(Request $request)
    {
        $filterId = $request->get('id'); // Optional ID filter

        // Query categories with optional filtering by parent ID
        if ($filterId) {
            $categories = ProductCategory::where('id', $filterId)
                ->orWhere('parent_id', $filterId)
                ->get();
        } else {
            $categories = ProductCategory::all();
        }

        // Transform categories into a nested parent-child structure
        $categoriesTree = $this->buildCategoryTree($categories);

        return response()->json($categoriesTree);
    }

    /**
     * Build a nested category tree.
     *
     * @param \Illuminate\Database\Eloquent\Collection $categories
     * @param int|null $parentId
     * @return array
     */
    private function buildCategoryTree($categories, $parentId = null)
    {
        $tree = [];
        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $children = $this->buildCategoryTree($categories, $category->id);
                $tree[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'children' => $children,
                ];
            }
        }
        return $tree;
    }
}
