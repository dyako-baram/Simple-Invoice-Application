<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::where('user_id', Auth::id())->get();
        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
        ]);

        $supplier = Supplier::create(array_merge($validated, ['user_id' => Auth::id()]));

        return response()->json($supplier, 201);
    }

    public function show($id)
    {
        $supplier = Supplier::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
        ]);

        $supplier->update($validated);

        return response()->json($supplier);
    }

    public function destroy($id)
    {
        $supplier = Supplier::where('user_id', Auth::id())->findOrFail($id);
        $supplier->delete();

        return response()->json(null, 204);
    }
}
