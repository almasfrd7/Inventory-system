<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function store(Request $request)
    {
        $product = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products,code',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        return Product::create($product);
    }

    public function show(Product $product)
    {
        return $product;
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:255|unique:products,code,' . $product->id,
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $product->update($data);

        return $product;
    }

    /*
    ==========================================
    ADJUST PRODUCT STOCK
    ==========================================

    quantity uses a signed number:

    positive = add stock
    negative = remove stock

    Keeping this separate from update() makes
    stock movements explicit and prevents stock
    from being reduced below zero.
    */
    public function adjustStock(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|not_in:0',
        ]);

        $newStock = $product->stock + $data['quantity'];

        // Do not allow a stock adjustment below zero.
        if ($newStock < 0) {
            return response()->json([
                'message' => 'Stock cannot be reduced below zero.',
            ], 422);
        }

        $product->update([
            'stock' => $newStock,
        ]);

        return response()->json([
            'message' => 'Stock adjusted successfully.',
            'product' => $product,
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}
