<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
use Botble\Ecommerce\Http\Controllers\TempProductController;
use Botble\Ecommerce\Http\Controllers\TempProductStatusController;
use Botble\Ecommerce\Http\Controllers\TempContentController;
use Botble\Ecommerce\Http\Controllers\ProductController;
use Botble\Ecommerce\Http\Controllers\DocumentController;
use Botble\Base\Facades\AdminHelper;
use Botble\Ecommerce\Http\Controllers\ImportProductImageController; // Ensure this path is correct
use Illuminate\Support\Facades\Route;
use Botble\Ecommerce\Http\Controllers\SpecificationController;
use Botble\Ecommerce\Http\Controllers\ImportProductDescriptionController;
use Botble\Ecommerce\Http\Controllers\EliteShipmentController;








Route::get('temp-products', [TempProductController::class, 'index'])->name('temp-products.index');
Route::post('temp-products/pricing-approve', [TempProductController::class, 'approvePricingChanges'])->name('temp-products.admin_pricing_approve');
Route::post('temp-products/graphics-approve', [TempProductController::class, 'approveGraphicsChanges'])->name('temp-products.admin_graphics_approve');
Route::post('temp-products/content-approve', [TempProductController::class, 'approveContentChanges'])->name('temp-products.admin_content_approve');

Route::get('ecommerce/temp-products-status', [TempProductStatusController::class, 'index'])->name('ecommerce/temp-products-status.index');
Route::post('ecommerce/temp-products-status/update-pricing-changes', [TempProductStatusController::class, 'updatePricingChanges'])->name('temp-products.pricing_approve');
Route::post('ecommerce/temp-products-status/update-graphics-changes', [TempProductStatusController::class, 'updateGraphicsChanges'])->name('temp-products.graphics_update');
Route::post('ecommerce/temp-products-status/approve', [TempProductStatusController::class, 'approveChanges'])->name('temp-products.approve');


Route::get('ecommerce/temp-products-content', [TempContentController::class, 'index'])->name('ecommerce/temp-product-content.index');
Route::post('ecommerce/temp-products-content/pricing-approve', [TempContentController::class, 'approvePricingChanges'])->name('temp-product.pricing_approve');
Route::post('ecommerce/temp-products-content/approve', [TempContentController::class, 'approveChanges'])->name('temp-products.approve');

Route::post('/delete-document', [DocumentController::class, 'deleteDocument'])
     ->name('document.delete');

     AdminHelper::registerRoutes(function () {
         Route::group(['namespace' => 'Botble\ProductImages\Http\Controllers', 'prefix' => 'ecommerce'], function () {
             Route::group(['prefix' => 'product-images', 'as' => 'product-images.'], function () {
                 Route::get('/import', [ImportProductImageController::class, 'index'])->name('import.index');
                 Route::post('/import', [ImportProductImageController::class, 'store'])->name('import.store');
             });
         });
     });
     Route::post('product-images/import/validate', [ImportProductImageController::class, 'validateImport'])->name('product-images.import.validate');
     Route::post('product-images/import/store', [ImportProductImageController::class, 'storeImport'])->name('product-images.import.store');

// Route::get('/import', [ImportProductImageController::class, 'index'])->name('import.index');
// Route::post('/import', [ImportProductImageController::class, 'store'])->name('import.store');
Route::group(['namespace' => 'Botble\ProductImages\Http\Controllers', 'prefix' => 'ecommerce'], function () {
    Route::group(['prefix' => 'product-images', 'as' => 'product-images.'], function () {
        Route::get('/import', [ImportProductImageController::class, 'index'])->name('import.index');
        Route::post('/import', [ImportProductImageController::class, 'store'])->name('import.store');
    });
});

Route::get('specifications/upload', [SpecificationController::class, 'showUploadForm'])->name('specifications.upload.form');
Route::post('specifications/upload', [SpecificationController::class, 'upload'])->name('specifications.upload');
// Route::group(['middleware' => ['auth']], function () {
//     Route::get('specifications/upload', [SpecificationController::class, 'showUploadForm'])->name('specifications.upload.form');
//     Route::post('specifications/upload', [SpecificationController::class, 'upload'])->name('specifications.upload');
// });


Route::group(['namespace' => 'YourNamespace'], function () {
    Route::get('/products/search-sku', [ProductController::class, 'searchBySku'])->name('products.search-sku');
});

Route::get('/products/search-sku', [ProductController::class, 'searchBySku'])
    ->name('products.search-sku');





    // Define the route for the create form
    Route::get('admin/ecommerce/create-shipment', [EliteShipmentController::class, 'create'])->name('eliteshipment.create');

    // Define the route to handle form submission
    Route::post('admin/ecommerce/store-shipment', [EliteShipmentController::class, 'store'])->name('eliteshipment.store');

