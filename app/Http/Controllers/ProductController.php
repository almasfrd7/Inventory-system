<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
// List all products without search, filter, sort or pagination.
// public function index()
//     {
//         return Product::all();
//     }


    /*
    ==========================================
    LOW STOCK SETTING
    ==========================================

    A product with stock from 1 to this value is
    "low stock". Zero stock is "out of stock".

    Keep this the same as LOW_STOCK_THRESHOLD in
    inventory.blade.php.
    */
    private const LOW_STOCK_THRESHOLD = 5;


    /*
    ==========================================
    LIST PRODUCTS (SEARCH / FILTER / SORT / PAGINATE)
    ==========================================

    GET /api/products

    Optional query parameters:

    search    = text to find in name, code or description
    status    = in_stock | low_stock | out_of_stock
    sort_by   = id | name | code | price | stock | created_at
    sort_dir  = asc | desc
    per_page  = 5 | 10 | 50
    page      = page number

    Example:

    /api/products?search=key&status=low_stock&sort_by=price&sort_dir=desc&per_page=5&page=2
    */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:in_stock,low_stock,out_of_stock',
            'sort_by' => 'nullable|in:id,name,code,price,stock,created_at',
            'sort_dir' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|in:5,10,50',
        ]);

        $query = Product::query();

        /*
        SEARCH

        Looks in name, code and description.
        % and _ are escaped so they are searched
        as normal characters, not LIKE wildcards.
        */
        if (!empty($filters['search'])) {

            $search = addcslashes($filters['search'], '%_\\');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });

        }

        /*
        FILTER BY STOCK STATUS
        */
        if (!empty($filters['status'])) {

            switch ($filters['status']) {

                case 'out_of_stock':
                    $query->where('stock', 0);
                    break;

                case 'low_stock':
                    $query->where('stock', '>', 0)
                        ->where('stock', '<=', self::LOW_STOCK_THRESHOLD);
                    break;

                case 'in_stock':
                    $query->where('stock', '>', self::LOW_STOCK_THRESHOLD);
                    break;

            }

        }

        /*
        SORT

        When sorting by something other than id, id is
        added as a second sort so rows with the same
        value always stay in the same order between pages.
        */
        $sortBy = $filters['sort_by'] ?? 'id';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        $query->orderBy($sortBy, $sortDir);

        if ($sortBy !== 'id') {
            $query->orderBy('id', 'asc');
        }

        /*
        PAGINATE

        Returns JSON like:

        {
            "success": true,
            "data": {
                "data": [ ...products... ],
                "current_page": 1,
                "last_page": 4,
                "per_page": 10,
                "total": 37,
                "from": 1,
                "to": 10,
            }
        }
        */
        return response()->json([
            'success' => true,
            'data' => $query
                ->paginate($filters['per_page'] ?? 10)
                ->withQueryString(),
        ], 200);
    }


    /*
    ==========================================
    PRODUCT STATISTICS
    ==========================================

    GET /api/products/stats

    Used by the dashboard. The list endpoint is
    paginated now, so the dashboard can no longer
    count everything from it.
    */
    public function stats()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => Product::count(),
                'total_stock' => (int) Product::sum('stock'),
                'low_stock' => Product::where('stock', '>', 0)
                    ->where('stock', '<=', self::LOW_STOCK_THRESHOLD)
                    ->count(),
                'out_of_stock' => Product::where('stock', 0)->count(),
            ],
        ], 200);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products,code',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product,
        ], 200);
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

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $product,
        ], 200);
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
                'success' => false,
                'data' => null,
                'message' => 'Stock cannot be reduced below zero.',
            ], 422);
        }

        $product->update([
            'stock' => $newStock,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted successfully.',
            'data' => $product,
        ], 200);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ], 200);
    }
}