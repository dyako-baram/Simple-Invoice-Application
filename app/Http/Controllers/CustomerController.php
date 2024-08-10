<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index()
    {
        try {
            $customers = Customer::where('user_id', Auth::id())->get();
            return response()->json($customers);
        } catch (\Exception $e) {
            Log::error('Error fetching customers: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch customers'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
                'balance' => 'required|numeric|min:0',
            ]);

            $customer = Customer::create(array_merge($validated, ['user_id' => Auth::id()]));

            return response()->json($customer, 201);
        } catch (\Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create customer'], 500);
        }
    }

    public function show($id)
    {
        try {
            $customer = Customer::where('user_id', Auth::id())->findOrFail($id);
            return response()->json($customer);
        } catch (\Exception $e) {
            Log::error('Error fetching customer: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch customer'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::where('user_id', Auth::id())->findOrFail($id);

            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
                'balance' => 'required|numeric|min:0',
            ]);

            $customer->update($validated);

            return response()->json($customer);
        } catch (\Exception $e) {
            Log::error('Error updating customer: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update customer'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $customer = Customer::where('user_id', Auth::id())->findOrFail($id);
            $customer->invoices()->delete();
            
            $customer->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting customer: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete customer'], 500);
        }
    }
}
