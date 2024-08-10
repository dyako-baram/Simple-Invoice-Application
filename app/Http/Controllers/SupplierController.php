<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Supplier;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    public function index()
    {
        try {
            $suppliers = Supplier::where('user_id', Auth::id())->get();
            return response()->json($suppliers);
        } catch (\Exception $e) {
            Log::error('Error fetching suppliers: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch suppliers'], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
        ]);

        try {
            $supplier = Supplier::create(array_merge($validated, ['user_id' => Auth::id()]));
            return response()->json($supplier, 201);
        } catch (\Exception $e) {
            Log::error('Error creating supplier: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create supplier'], 500);
        }
    }

    public function show($id)
    {
        try {
            $supplier = Supplier::where('user_id', Auth::id())->findOrFail($id);
            return response()->json($supplier);
        } catch (\Exception $e) {
            Log::error('Error fetching supplier: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch supplier'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $supplier = Supplier::where('user_id', Auth::id())->findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
            ]);

            $supplier->update($validated);

            return response()->json($supplier);
        } catch (\Exception $e) {
            Log::error('Error updating supplier: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update supplier'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $supplier = Supplier::where('user_id', Auth::id())->findOrFail($id);
            $supplier->products()->delete();
            $supplier->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting supplier: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete supplier'], 500);
        }
    }
}
