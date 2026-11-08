<?php

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Database\Seeders\CategorySeeder;

function createStoreProduct(int $stock = 3): Product
{
    $category = Category::query()->create(['name' => 'Blusas']);

    return Product::query()->create([
        'code' => 'PRD-TEST001',
        'category_id' => $category->id,
        'name' => 'Blusa elegante',
        'purchase_price' => '65.00',
        'sale_price' => '120.00',
        'max_discount' => '30.00',
        'stock' => $stock,
        'received_at' => today(),
        'is_active' => true,
    ]);
}

test('a sale keeps historical prices and cancellation restores stock', function () {
    $user = User::factory()->create();
    $product = createStoreProduct();

    $response = $this->actingAs($user)->post(route('sales.store'), [
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'discount' => '20.00',
        ]],
    ]);

    $sale = Sale::query()->firstOrFail();
    $response->assertRedirect(route('sales.receipt', $sale));
    $this->get(route('sales.receipt', $sale))->assertOk();
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
        'subtotal' => '240.00',
        'discount_total' => '40.00',
        'total' => '200.00',
        'cost_total' => '130.00',
        'profit_total' => '70.00',
    ]);

    $detail = SaleDetail::query()->firstOrFail();
    expect($detail->unit_purchase_price)->toBe('65.00')
        ->and($detail->unit_sale_price)->toBe('120.00')
        ->and($detail->unit_discount)->toBe('20.00');

    $product->update(['purchase_price' => '90.00', 'sale_price' => '150.00']);
    expect($detail->refresh()->unit_purchase_price)->toBe('65.00');

    $this->actingAs($user)->post(route('sales.cancel', $sale), [
        'reason' => 'El cliente devolvió la compra',
    ])->assertRedirect(route('sales.show', $sale));

    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
    $this->assertDatabaseHas('sales', ['id' => $sale->id, 'status' => 'cancelled']);
    expect(InventoryMovement::query()->where('sale_id', $sale->id)->pluck('type')->all())
        ->toContain('sale', 'sale_return');
});

test('sales history can be filtered by month', function () {
    $user = User::factory()->create();

    foreach (['2026-10-15 10:00:00', '2026-11-06 10:00:00'] as $index => $date) {
        $sale = Sale::query()->create([
            'number' => 'SALE-MONTH-'.$index,
            'user_id' => $user->id,
            'subtotal' => 100,
            'discount_total' => 0,
            'total' => 100,
            'cost_total' => 50,
            'profit_total' => 50,
        ]);
        $sale->created_at = $date;
        $sale->saveQuietly();
    }

    $this->actingAs($user)
        ->get(route('sales.index', ['month' => '2026-10']))
        ->assertOk()
        ->assertSee('SALE-MONTH-0')
        ->assertDontSee('SALE-MONTH-1');
});

test('sales reject excessive discounts and quantities without changing inventory', function () {
    $user = User::factory()->create();
    $product = createStoreProduct(stock: 1);
    $this->actingAs($user);

    $this->from(route('sales.create'))->post(route('sales.store'), [
        'items' => [['product_id' => $product->id, 'quantity' => 1, 'discount' => '31.00']],
    ])->assertSessionHasErrors('items');

    $this->from(route('sales.create'))->post(route('sales.store'), [
        'items' => [['product_id' => $product->id, 'quantity' => 2, 'discount' => '0.00']],
    ])->assertSessionHasErrors('items');

    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
    expect(Sale::query()->count())->toBe(0);
});

test('editing a product cannot change stock outside the movement ledger', function () {
    $user = User::factory()->create();
    $product = createStoreProduct(stock: 2);

    $this->actingAs($user)->put(route('products.update', $product), [
        'name' => $product->name,
        'category_id' => $product->category_id,
        'purchase_price' => $product->purchase_price,
        'sale_price' => $product->sale_price,
        'max_discount' => $product->max_discount,
        'received_at' => today()->toDateString(),
        'stock' => 999,
        'is_active' => '1',
    ])->assertRedirect(route('products.show', $product));

    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 2]);
    expect(InventoryMovement::query()->where('product_id', $product->id)->count())->toBe(0);
});

test('seeded categories and authenticated module pages are available', function () {
    $this->seed(CategorySeeder::class);
    $user = User::factory()->create();
    $product = Product::query()->create([
        'code' => 'PRD-PAGES01',
        'category_id' => Category::query()->firstOrFail()->id,
        'name' => 'Prenda de prueba',
        'purchase_price' => '10.00',
        'sale_price' => '20.00',
        'max_discount' => '2.00',
        'stock' => 1,
        'received_at' => today(),
        'is_active' => true,
    ]);

    expect(Category::query()->count())->toBe(6);

    $this->actingAs($user);
    foreach ([
        route('dashboard'),
        route('categories.index'),
        route('products.index'),
        route('products.index', ['view' => 'cards']),
        route('products.create'),
        route('products.show', $product),
        route('products.edit', $product),
        route('inventory.index'),
        route('sales.index'),
        route('sales.create'),
        route('reports.index'),
        route('business-settings.edit'),
    ] as $url) {
        $this->get($url)->assertOk();
    }
});

test('deleting a completed sale removes it and restores stock', function () {
    $user = User::factory()->create();
    $product = createStoreProduct();

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [['product_id' => $product->id, 'quantity' => 2, 'discount' => '0']],
    ]);
    expect($product->fresh()->stock)->toBe(1);

    $sale = Sale::query()->firstOrFail();
    $this->actingAs($user)->delete(route('sales.destroy', $sale))->assertRedirect(route('sales.index'));

    expect(Sale::count())->toBe(0)
        ->and(SaleDetail::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(3);
});

test('deleting a product removes it and its investment but keeps past sales', function () {
    $user = User::factory()->create();
    $product = createStoreProduct();

    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [['product_id' => $product->id, 'quantity' => 1, 'discount' => '0']],
    ]);
    $this->actingAs($user)->delete(route('products.destroy', $product))->assertRedirect(route('products.index'));

    expect(Product::find($product->id))->toBeNull()
        ->and(InventoryMovement::query()->where('type', 'entry')->count())->toBe(0)
        ->and(SaleDetail::query()->firstOrFail()->product_name)->toBe('Blusa elegante')
        ->and(SaleDetail::query()->firstOrFail()->product_id)->toBeNull();

    $sale = Sale::query()->firstOrFail();
    $this->actingAs($user)->post(route('sales.cancel', $sale), ['reason' => 'Prueba de anulación'])->assertSessionHasNoErrors();
    expect($sale->fresh()->status)->toBe('cancelled');
    $this->actingAs($user)->delete(route('sales.destroy', $sale));
    expect(Sale::count())->toBe(0);
});

test('empty categories can be deleted but categories with products cannot', function () {
    $user = User::factory()->create();
    $product = createStoreProduct();
    $empty = Category::query()->create(['name' => 'Vacía']);

    $this->actingAs($user)->delete(route('categories.destroy', $empty));
    expect(Category::find($empty->id))->toBeNull();

    $this->actingAs($user)->delete(route('categories.destroy', $product->category))->assertSessionHasErrors('category');
    expect(Category::find($product->category_id))->not->toBeNull();
});

test('deleting a manual movement reverts its effect on stock', function () {
    $user = User::factory()->create();
    $product = createStoreProduct();

    $this->actingAs($user)->post(route('inventory.store'), ['product_id' => $product->id, 'type' => 'entry', 'quantity' => 5, 'reason' => 'Reposición']);
    expect($product->fresh()->stock)->toBe(8);
    $entry = InventoryMovement::query()->latest('id')->firstOrFail();
    $this->actingAs($user)->delete(route('inventory.destroy', $entry));
    expect($product->fresh()->stock)->toBe(3)->and(InventoryMovement::count())->toBe(0);

    $this->actingAs($user)->post(route('inventory.store'), ['product_id' => $product->id, 'type' => 'withdrawal', 'quantity' => 2, 'reason' => 'Daño']);
    expect($product->fresh()->stock)->toBe(1);
    $this->actingAs($user)->delete(route('inventory.destroy', InventoryMovement::query()->firstOrFail()));
    expect($product->fresh()->stock)->toBe(3);
});

test('a movement cannot be deleted if reverting it would leave negative stock', function () {
    $user = User::factory()->create();
    $product = createStoreProduct();

    $this->actingAs($user)->post(route('inventory.store'), ['product_id' => $product->id, 'type' => 'entry', 'quantity' => 5, 'reason' => 'Reposición']);
    $entry = InventoryMovement::query()->firstOrFail();
    $product->update(['stock' => 2]);

    $this->actingAs($user)->delete(route('inventory.destroy', $entry))->assertSessionHasErrors('movement');
    expect($product->fresh()->stock)->toBe(2)->and(InventoryMovement::count())->toBe(1);
});

test('a manual movement does not require a reason', function () {
    $user = User::factory()->create();
    $product = createStoreProduct();

    $this->actingAs($user)->post(route('inventory.store'), ['product_id' => $product->id, 'type' => 'entry', 'quantity' => 2])->assertSessionHasNoErrors();

    expect($product->fresh()->stock)->toBe(5)
        ->and(InventoryMovement::query()->firstOrFail()->reason)->toBe('Entrada de inventario');
});

test('monthly report shows totals and exports to excel and pdf', function () {
    $user = User::factory()->create();
    $product = createStoreProduct();
    InventoryMovement::query()->create([
        'product_id' => $product->id, 'user_id' => $user->id, 'type' => 'entry', 'quantity' => 3,
        'stock_before' => 0, 'stock_after' => 3, 'reason' => 'Inicial',
    ]);
    $this->actingAs($user)->post(route('sales.store'), [
        'items' => [['product_id' => $product->id, 'quantity' => 1, 'discount' => '0']],
    ]);

    $this->actingAs($user)->get(route('reports.index'))->assertOk()->assertSee('Total invertido')->assertSee('195.00')->assertSee('120.00')->assertSee('55.00');
    $this->actingAs($user)->get(route('reports.excel'))->assertOk()->assertDownload('reporte-ventas-'.now()->format('Y-m').'.xlsx');
    $this->actingAs($user)->get(route('reports.pdf'))->assertOk()->assertDownload('reporte-ventas-'.now()->format('Y-m').'.pdf');
});
