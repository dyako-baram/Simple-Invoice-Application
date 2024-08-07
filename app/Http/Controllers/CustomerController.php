<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::where('user_id', Auth::id())->get();
        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'balance' => 'required|numeric|min:0',
        ]);

        $customer = Customer::create(array_merge($validated, ['user_id' => Auth::id()]));

        return response()->json($customer, 201);
    }

    public function show($id)
    {
        $customer = Customer::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($customer);
    }

    public function update(Request $request, $id)
    {
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
    }

    public function destroy($id)
    {
        $customer = Customer::where('user_id', Auth::id())->findOrFail($id);
        $customer->delete();

        return response()->json(null, 204);
    }
}
