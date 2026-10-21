<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Mechanical Keyboard',
            'code' => 'KB001',
            'price' => 159.90,
            'stock' => 15,
            'description' => 'RGB mechanical keyboard',
        ], $overrides);
    }

    // ---------- CRUD ----------

    public function test_can_create_product(): void
    {
        $this->postJson('/api/products', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'KB001')
            ->assertJsonPath('data.price', 159.9)
            ->assertJsonPath('data.stock_status', 'in_stock');

        $this->assertDatabaseHas('products', ['code' => 'KB001']);
    }

    public function test_can_show_product(): void
    {
        $product = Product::factory()->create();

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id);
    }

    public function test_can_update_product_with_put(): void
    {
        $product = Product::factory()->create();

        $this->putJson("/api/products/{$product->id}", $this->validPayload(['name' => 'Updated']))
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    }

    public function test_put_requires_all_fields_but_patch_does_not(): void
    {
        $product = Product::factory()->create();

        $this->putJson("/api/products/{$product->id}", ['name' => 'Only name'])
            ->assertStatus(422);

        $this->patchJson("/api/products/{$product->id}", ['name' => 'Only name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Only name');
    }

    public function test_update_allows_keeping_own_code(): void
    {
        $product = Product::factory()->create(['code' => 'KEEP-1']);

        $this->patchJson("/api/products/{$product->id}", ['code' => 'KEEP-1'])->assertOk();
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->deleteJson("/api/products/{$product->id}")->assertOk();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    // ---------- Validation ----------

    public function test_missing_required_fields_return_422(): void
    {
        $this->postJson('/api/products', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['name', 'code', 'price', 'stock'], 'errors');
    }

    public function test_duplicate_code_is_rejected(): void
    {
        Product::factory()->create(['code' => 'DUP-1']);

        $this->postJson('/api/products', $this->validPayload(['code' => 'DUP-1']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code'], 'errors');
    }

    public function test_negative_price_and_stock_are_rejected(): void
    {
        $this->postJson('/api/products', $this->validPayload(['price' => -1, 'stock' => -5]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price', 'stock'], 'errors');
    }

    // ---------- Not found ----------

    public function test_unknown_product_returns_json_404(): void
    {
        $this->getJson('/api/products/9999')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Resource not found.');

        $this->putJson('/api/products/9999', $this->validPayload())->assertNotFound();
        $this->deleteJson('/api/products/9999')->assertNotFound();
    }

    public function test_wrong_method_keeps_405_status(): void
    {
        $this->postJson('/api/products/stats')->assertStatus(405)->assertJsonPath('success', false);
    }

    // ---------- List: search / filter / sort / pagination ----------

    public function test_search_filters_by_name_code_and_description(): void
    {
        Product::factory()->create(['name' => 'Gaming Keyboard', 'code' => 'AAA-1', 'description' => 'x']);
        Product::factory()->create(['name' => 'Mouse', 'code' => 'BBB-2', 'description' => 'x']);

        $this->getJson('/api/products?search=Keyboard')->assertJsonCount(1, 'data');
        $this->getJson('/api/products?search=BBB')->assertJsonCount(1, 'data');
    }

    public function test_status_filter(): void
    {
        Product::factory()->create(['stock' => 0]);
        Product::factory()->create(['stock' => 3]);
        Product::factory()->create(['stock' => 50]);

        $this->getJson('/api/products?status=out_of_stock')->assertJsonCount(1, 'data');
        $this->getJson('/api/products?status=low_stock')->assertJsonCount(1, 'data');
        $this->getJson('/api/products?status=in_stock')->assertJsonCount(1, 'data');
    }

    public function test_sorting_by_price_descending(): void
    {
        Product::factory()->create(['price' => 10]);
        Product::factory()->create(['price' => 30]);
        Product::factory()->create(['price' => 20]);

        $prices = $this->getJson('/api/products?sort_by=price&sort_dir=desc')->json('data.*.price');

        $this->assertSame([30.0, 20.0, 10.0], $prices);
    }

    public function test_pagination_meta(): void
    {
        Product::factory()->count(12)->create();

        $this->getJson('/api/products?per_page=5&page=3')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonPath('meta.current_page', 3);
    }

    public function test_invalid_sort_column_is_rejected(): void
    {
        $this->getJson('/api/products?sort_by=password')->assertStatus(422);
    }

    // ---------- Stats ----------

    public function test_stats(): void
    {
        Product::factory()->create(['stock' => 0]);
        Product::factory()->create(['stock' => 3]);
        Product::factory()->create(['stock' => 20]);

        $this->getJson('/api/products/stats')
            ->assertOk()
            ->assertJsonPath('data.total_products', 3)
            ->assertJsonPath('data.total_stock', 23)
            ->assertJsonPath('data.low_stock', 1)
            ->assertJsonPath('data.out_of_stock', 1);
    }

    public function test_stats_on_empty_table(): void
    {
        $this->getJson('/api/products/stats')
            ->assertOk()
            ->assertJsonPath('data.total_products', 0)
            ->assertJsonPath('data.total_stock', 0);
    }

    // ---------- Stock adjustment ----------

    public function test_can_add_and_remove_stock_and_movements_are_logged(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->postJson("/api/products/{$product->id}/adjust-stock", ['quantity' => 5, 'reason' => 'Delivery'])
            ->assertOk()
            ->assertJsonPath('data.stock', 15);

        $this->postJson("/api/products/{$product->id}/adjust-stock", ['quantity' => -3])
            ->assertOk()
            ->assertJsonPath('data.stock', 12);

        $this->assertDatabaseCount('stock_movements', 2);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => 5,
            'stock_before' => 10,
            'stock_after' => 15,
            'reason' => 'Delivery',
        ]);
    }

    public function test_cannot_reduce_stock_below_zero(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        $this->postJson("/api/products/{$product->id}/adjust-stock", ['quantity' => -5])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity'], 'errors');

        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_zero_or_non_integer_adjustment_is_rejected(): void
    {
        $product = Product::factory()->create();

        $this->postJson("/api/products/{$product->id}/adjust-stock", ['quantity' => 0])->assertStatus(422);
        $this->postJson("/api/products/{$product->id}/adjust-stock", ['quantity' => 1.5])->assertStatus(422);
    }
}
