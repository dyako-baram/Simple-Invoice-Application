<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::where('user_id', Auth::id())->with('supplier')->get();
            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch products'], 500);
        }
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

        try {
            if ($request->hasFile('product_image')) {
                $path = $request->file('product_image')->store('product_images');
                $validated['product_image'] = $path;
            }

            $product = Product::create(array_merge($validated, ['user_id' => Auth::id()]));

            return response()->json($product, 201);
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create product'], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::where('user_id', Auth::id())->with('supplier')->findOrFail($id);
            return response()->json($product);
        } catch (\Exception $e) {
            Log::error('Error fetching product: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch product'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update product'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::where('user_id', Auth::id())->findOrFail($id);

            if ($product->product_image) {
                Storage::delete($product->product_image);
            }

            $product->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete product'], 500);
        }
    }
}
