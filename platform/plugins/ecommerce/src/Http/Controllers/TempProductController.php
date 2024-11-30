<?php

namespace Botble\Ecommerce\Http\Controllers;
use Carbon\Carbon; // Make sure to import Carbon at the top
use Botble\Ecommerce\Models\TempProduct; // Make sure this is the correct model namespace
use Botble\Ecommerce\Models\UnitOfMeasurement;
use Botble\Marketplace\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // Import Schema facade
class TempProductController extends BaseController
{
	public function index()
	{
		// Fetch all temporary product changes
		$tempPricingProducts = TempProduct::where('role_id', 22)->where('status', '!=', 'pending')->get();
		$tempContentProducts = TempProduct::where('role_id', 18)->where('status', '!=', 'pending')->get();
		$tempGraphicsProducts = TempProduct::where('role_id', 19)->where('status', '!=', 'pending')->get();

		$unitOfMeasurements = UnitOfMeasurement::pluck('name', 'id')->toArray();
		$stores = Store::pluck('name', 'id')->toArray();

		$approvalStatuses = [
			'pending' => 'Pending',
			'approved' => 'Approved',
			'rejected' => 'Rejected',
		];

		return view('plugins/ecommerce::products.partials.temp-product-changes', compact('tempPricingProducts', 'tempContentProducts', 'tempGraphicsProducts', 'unitOfMeasurements', 'stores', 'approvalStatuses'));
	}


	public function approvePricingChanges(Request $request)
	{
		logger()->info('approvePricingChanges method called.');
		logger()->info('Request Data: ', $request->all());
		$request->validate([
			'approval_status' => 'required',
		]);

		if($request->initial_approval_status=='pending' && $request->approval_status=='approved') {
			$tempProduct = TempProduct::find($request->id);
			$input = $request->all();
			unset($input['_token'], $input['id'], $input['initial_approval_status'], $input['approval_status'], $input['margin']);
			$tempProduct->product->update($input);
			$tempProduct->update(['approval_status' => $request->approval_status]);
		}

		return redirect()->route('temp-products.index')->with('success', 'Product changes approved and updated successfully.');
	}


	public function approveChanges(Request $request)
	{
		logger()->info('approveChanges method called.');
		logger()->info('Request Data: ', $request->all());

		$request->validate([
			'approval_status' => 'required|array',
		]);

		foreach ($request->approval_status as $changeId => $status) {
			logger()->info("Updating status for Change ID: {$changeId} to Status: {$status}");

			$tempProduct = TempProduct::find($changeId);

			if ($tempProduct) {
				$tempProduct->update(['approval_status' => $status]);

				if ($status === 'approved') {
					$productData = $tempProduct->toArray();

					unset($productData['id']);
					unset($productData['approval_status']);
					unset($productData['product_id']);

					// Convert datetime fields to the correct format
					if (isset($productData['created_at'])) {
						$productData['created_at'] = Carbon::parse($productData['created_at'])->format('Y-m-d H:i:s');
					}
					if (isset($productData['updated_at'])) {
						$productData['updated_at'] = Carbon::parse($productData['updated_at'])->format('Y-m-d H:i:s');
					}

					$existingFields = Schema::getColumnListing('ec_products');
					$fieldsToUpdate = array_intersect_key($productData, array_flip($existingFields));

					$fieldsToUpdate = array_filter($fieldsToUpdate, function ($value) {
						return !is_null($value) && $value !== '';
					});

					if (!empty($fieldsToUpdate)) {
						$updated = DB::table('ec_products')
						->where('id', $tempProduct->product_id)
						->update($fieldsToUpdate);

						if ($updated) {
							$tempProduct->delete();
						} else {
							logger()->warning("No product found with ID: {$tempProduct->product_id}");
						}
					} else {
						logger()->info("No valid fields to update for Change ID: {$changeId}");
					}
				}
			}
		}
		return redirect()->route('temp-products.index')->with('success', 'Product changes approved and updated successfully.');
	}
}