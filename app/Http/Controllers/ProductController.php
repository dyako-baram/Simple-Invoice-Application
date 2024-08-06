<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        return Product::where('user_id', Auth::id())->with('supplier')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|max:255|unique:products',
            'quantity_on_hand' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'supplier_id' => 'required|exists:suppliers,id',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('product_image')) {
            $path = $request->file('product_image')->store('product_images');
            $validated['product_image'] = $path;
        }

        $product = Product::create(array_merge($validated, ['user_id' => Auth::id()]));

        return response()->json($product, 201);
    }

    public function show($id)
    {
        return Product::where('user_id', Auth::id())->with('supplier')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $product = Product::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|max:255|unique:products,barcode,' . $product->id,
            'quantity_on_hand' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'supplier_id' => 'required|exists:suppliers,id',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('product_image')) {
            $path = $request->file('product_image')->store('product_images');
            $validated['product_image'] = $path;
            if ($product->product_image) {
                Storage::delete($product->product_image);
            }
        }

        $product->update($validated);

        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::where('user_id', Auth::id())->findOrFail($id);
        if ($product->product_image) {
            Storage::delete($product->product_image);
        }
        $product->delete();

        return response()->json(null, 204);
    }
}
