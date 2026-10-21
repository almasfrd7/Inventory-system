<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * GET /api/products
     *
     * Response: { data: [...], links: {...}, meta: {...}, success: true }
     */
    public function index(IndexProductRequest $request)
    {
        $filters = $request->validated();

        $sortBy = $filters['sort_by'] ?? 'id';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        $products = Product::query()
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->orderBy($sortBy, $sortDir)
            // Stable order between pages when many rows share a value.
            ->when($sortBy !== 'id', fn ($q) => $q->orderBy('id'))
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();

        return ProductResource::collection($products)->additional(['success' => true]);
    }

    /**
     * GET /api/products/stats  (single query instead of four)
     */
    public function stats(): JsonResponse
    {
        $stats = Product::toBase()->selectRaw('
            COUNT(*) as total_products,
            COALESCE(SUM(stock), 0) as total_stock,
            COALESCE(SUM(CASE WHEN stock > 0 AND stock <= ? THEN 1 ELSE 0 END), 0) as low_stock,
            COALESCE(SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END), 0) as out_of_stock
        ', [config('inventory.low_stock_threshold')])->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => (int) $stats->total_products,
                'total_stock' => (int) $stats->total_stock,
                'low_stock' => (int) $stats->low_stock,
                'out_of_stock' => (int) $stats->out_of_stock,
            ],
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => new ProductResource($product),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * POST /api/products/{product}/adjust-stock
     *
     * The row is locked inside a transaction so two simultaneous
     * adjustments cannot overwrite each other, and every change is
     * written to stock_movements.
     */
    public function adjustStock(AdjustStockRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        $updated = DB::transaction(function () use ($data, $product, $request) {
            $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

            $before = $locked->stock;
            $after = $before + $data['quantity'];

            if ($after < 0) {
                return null;
            }

            $locked->update(['stock' => $after]);

            StockMovement::create([
                'product_id' => $locked->id,
                'user_id' => $request->user()?->id,
                'quantity' => $data['quantity'],
                'stock_before' => $before,
                'stock_after' => $after,
                'reason' => $data['reason'] ?? null,
            ]);

            return $locked;
        });

        if ($updated === null) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Stock cannot be reduced below zero.',
                'errors' => ['quantity' => ['Stock cannot be reduced below zero.']],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted successfully.',
            'data' => new ProductResource($updated),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }
}
