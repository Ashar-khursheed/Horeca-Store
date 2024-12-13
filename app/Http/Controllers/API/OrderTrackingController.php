<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Botble\Ecommerce\Http\Requests\Fronts\OrderTrackingRequest;
use Botble\Ecommerce\Models\Order;
use Illuminate\Http\JsonResponse;
use Botble\Ecommerce\Facades\EcommerceHelper;

class OrderTrackingController extends Controller
{
    public function trackOrder(OrderTrackingRequest $request): JsonResponse
    {
        if (!EcommerceHelper::isOrderTrackingEnabled()) {
            return response()->json(['message' => __('Order tracking is disabled')], 403);
        }

        $code = $request->input('order_id');

        $query = Order::query()
            ->where(function ($query) use ($code) {
                $query
                    ->where('code', $code)
                    ->orWhere('code', '#' . $code);
            })
            ->with(['address', 'products' , 'shipment'])
            ->select('*')
            ->when(EcommerceHelper::isLoginUsingPhone(), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query
                        ->whereHas('address', fn ($subQuery) => $subQuery->where('phone', $request->input('phone')))
                        ->orWhereHas('user', fn ($subQuery) => $subQuery->where('phone', $request->input('phone')));
                });
            }, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query
                        ->whereHas('address', fn ($subQuery) => $subQuery->where('email', $request->input('email')))
                        ->orWhereHas('user', fn ($subQuery) => $subQuery->where('email', $request->input('email')));
                });
            });

        $order = $query->first();

        if (!$order) {
            return response()->json(['message' => __('Order not found')], 404);
        }

        $order->load('payment');
        // Retrieve shipment status
        $shipment = $order->shipment; // Ensure the `shipment` relationship exists in your `Order` model
        $shipmentStatus = $shipment ? $shipment->status : __('No shipment information available');
        // Define all possible statuses
        $statuses = [
            'not_approved',
            'approved',
            'pending',
            'arrange_shipment',
            'ready_to_be_shipped_out',
            'picking',
            'delay_picking',
            'picked',
            'not_picked',
            'delivering',
            'delivered',
            'not_delivered',
            'audited',
            'canceled',
        ];

        return response()->json([
            'message' => __('Order found'),
            'shipment_status' => $shipmentStatus,
           
            'data' => $order,
            'all_statuses' => $statuses,
        ]);
    }
}
