<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Botble\Ecommerce\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AddressController extends Controller
{
    /**
     * Get all addresses for the authenticated user.
     */
    public function index()
    {
        Log::info('Entered index method in AddressController.');
        $userId = Auth::id(); // Get the authenticated user's ID

        if (!$userId) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

    
        $addresses = Address::where('customer_id', $userId)->get();
    
        Log::info('Fetched addresses: ', ['addresses' => $addresses]);
    
        return response()->json([
            'message' => 'Fetched addresses',
            'data' => $addresses
        ]);
    }

    /**
     * Add a new address.
     */
    public function store(Request $request)
    {
        Log::info('Entered store method for AddressController.');
    
        $userId = Auth::id(); // Get the authenticated user's ID

        if (!$userId) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

    
        try {
            // Validate incoming request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'country' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'zip_code' => 'required|string|max:10',
                'is_default' => 'nullable|boolean',
            ]);
    
            Log::info('Validated data: ', $validatedData);
    
            // Merge user ID with validated request data
            $addressData = array_merge($validatedData, ['customer_id' => Auth::user()->id]);
    
            Log::info('Merging data for creation: ', $addressData);
    
            // Create new address
            $address = Address::create($addressData);
    
            Log::info('Address successfully created: ', ['address' => $address]);
    
            return response()->json([
                'message' => 'Address created successfully.',
                'data' => $address,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating address: ', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'An unexpected error occurred.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    
    

    /**
     * Update an existing address by ID.
     */
    public function update(Request $request, $id)
    {
        Log::info('Entered update method with ID: ', ['id' => $id]);
    
        $address = Address::where('id', $id)->first();
    
        if (!$address) {
            Log::warning('Address not found.', ['id' => $id]);
            return response()->json(['error' => 'Address not found'], 404);
        }
    
        $address->update($request->all());
        Log::info('Address updated: ', ['address' => $address]);
    
        return response()->json(['message' => 'Address updated successfully.', 'data' => $address]);
    }
    

    /**
     * Delete an address by ID.
     */
    public function destroy($id)
    {
        Log::info('Entered destroy method with ID: ', ['id' => $id]);
    
        $address = Address::where('id', $id)->first();
    
        if (!$address) {
            Log::warning('Address not found for deletion.', ['id' => $id]);
            return response()->json(['error' => 'Address not found'], 404);
        }
    
        $address->delete();
        Log::info('Address deleted: ', ['id' => $id]);
    
        return response()->json(['message' => 'Address deleted successfully']);
    }
    
}
