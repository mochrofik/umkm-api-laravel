<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerAddressController extends Controller
{
    /**
     * Get all addresses for the authenticated customer.
     */
    public function index()
    {
        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->first();

        if (!$customer) {
            return $this->errorResponse('Customer not found', null, 404);
        }

        $addresses = CustomerAddress::where('customer_id', $customer->id)
            ->orderBy('is_primary', 'desc')
            ->get();

        return $this->successResponse('Addresses retrieved successfully', $addresses);
    }

    /**
     * Store a new address.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'province' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'subdistrict' => 'required|string',
            'postal_code' => 'required|string|max:5',
            'address_details' => 'required|string|max:150',
            'label' => 'nullable|string|max:50',
            'is_primary' => 'nullable|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }

        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->first();

        if (!$customer) {
            return $this->errorResponse('Customer not found', null, 404);
        }

        // Check if this is the first address
        $isFirst = !CustomerAddress::where('customer_id', $customer->id)->exists();
        $isPrimary = $isFirst || $request->is_primary;

        if ($isPrimary) {
            CustomerAddress::where('customer_id', $customer->id)->update(['is_primary' => false]);
        }

        $address = CustomerAddress::create(array_merge($request->all(), [
            'customer_id' => $customer->id,
            'is_primary' => $isPrimary
        ]));

        return $this->successResponse('Address created successfully', $address, 201);
    }

    /**
     * Update an existing address.
     */
    public function update(Request $request, $id)
    {
        $address = CustomerAddress::find($id);

        if (!$address) {
            return $this->errorResponse('Address not found', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'province' => 'nullable|string',
            'city' => 'nullable|string',
            'district' => 'nullable|string',
            'subdistrict' => 'nullable|string',
            'postal_code' => 'nullable|string|max:5',
            'address_details' => 'nullable|string|max:150',
            'label' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', $validator->errors(), 422);
        }

        $address->update($request->all());

        return $this->successResponse('Address updated successfully', $address);
    }

    /**
     * Delete an address.
     */
    public function destroy($id)
    {
        $address = CustomerAddress::find($id);

        if (!$address) {
            return $this->errorResponse('Address not found', null, 404);
        }

        if ($address->is_primary) {
            return $this->errorResponse('Cannot delete primary address. Set another one as primary first.', null, 400);
        }

        $address->delete();

        return $this->successResponse('Address deleted successfully');
    }

    /**
     * Set an address as primary.
     */
    public function setPrimary($id)
    {
        $address = CustomerAddress::find($id);

        if (!$address) {
            return $this->errorResponse('Address not found', null, 404);
        }

        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->first();

        if ($address->customer_id != $customer->id) {
            return $this->errorResponse('Unauthorized', null, 403);
        }

        // Set all addresses to false
        CustomerAddress::where('customer_id', $customer->id)->update(['is_primary' => false]);

        // Set this one to true
        $address->is_primary = true;
        $address->save();

        return $this->successResponse('Primary address updated successfully', $address);
    }
}
