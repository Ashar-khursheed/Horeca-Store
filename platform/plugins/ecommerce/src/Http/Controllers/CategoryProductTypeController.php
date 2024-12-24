<?php

namespace Botble\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Botble\Ecommerce\Models\ProductTypes;
use Botble\Ecommerce\Models\ProductCategory;

class CategoryProductTypeController extends BaseController
{
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		// Fetch all product types
		$categories = ProductCategory::with(['productTypes', 'specifications'])->paginate(20)->through(function ($category) {
			return [
				'id' => $category->id,
				'name' => $category->name,
				'product_types' => $category->productTypes ? $category->productTypes->pluck('name')->implode(', ') : '',
				'specifications' => $category->specifications ? $category->specifications->pluck('specification_name')->implode(', '):'',
			];
		});

		// dd($categories->toArray());
		return view('plugins/ecommerce::category-product-type.index', compact('categories'));
	}

	/**
	 * Display the specified resource.
	 */
	public function edit($id)
	{
		// Fetch the category with product types and specifications
		$category = ProductCategory::with(['productTypes', 'specifications'])->findOrFail($id);

		// Fetch all available product types for the multi-select
		$productTypes = ProductTypes::all(['id', 'name']);

		// Pass the data to the edit view
		return view('plugins/ecommerce::category-product-type.edit', compact('category', 'productTypes'));
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, $id)
	{
		$category = ProductCategory::findOrFail($id);

		// Update product types
		$category->productTypes()->sync($request->input('product_types', []));

		// Update specifications
		$category->specifications()->delete();
		foreach ($request->input('specifications', []) as $specification) {
			if (!empty($specification['name'])) {
				$category->specifications()->create([
					'specification_name' => $specification['name'],
					'specification_values' => implode('|', array_filter($specification['vals'], fn($val) => !is_null($val))),

				]);
			}
		}

		return redirect()->route('categoryFilter.index')->with('success', 'Category updated successfully.');
	}
}