<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search')->toString().'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $search)->orWhere('code', 'like', $search));
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('size'), fn ($query) => $query->where('size', $request->string('size')->toString()))
            ->when($request->input('availability') === 'available', fn ($query) => $query->where('is_active', true)->where('stock', '>', 0))
            ->when($request->input('availability') === 'out', fn ($query) => $query->where('is_active', true)->where('stock', 0))
            ->when($request->input('availability') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        return view('products.create', ['categories' => Category::query()->where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $initialStock = (int) $data['stock'];
        $imagePath = $request->file('image')?->store('products', 'public');

        DB::transaction(function () use ($data, $initialStock, $imagePath, $request) {
            $product = Product::create([
                ...$data,
                'max_discount' => $data['sale_price'],
                'code' => $this->newCode(),
                'stock' => $initialStock,
                'received_at' => $data['received_at'] ?? today(),
                'image_path' => $imagePath,
            ]);

            if ($initialStock > 0) {
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'type' => 'entry',
                    'quantity' => $initialStock,
                    'stock_before' => 0,
                    'stock_after' => $initialStock,
                    'reason' => 'Existencia inicial del producto',
                ]);
            }
        });

        return to_route('products.index')->with('success', 'La prenda se registró correctamente.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'inventoryMovements' => fn ($query) => $query->with('user')->latest()->limit(8)]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::query()->where('is_active', true)->orWhere('id', $product->category_id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate($this->rules($product));
        $previousImage = $product->image_path;
        $imagePath = $request->file('image')?->store('products', 'public');

        $product->update([
            ...$data,
            'max_discount' => $data['sale_price'],
            'image_path' => $imagePath ?? $product->image_path,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($imagePath && $previousImage) {
            Storage::disk('public')->delete($previousImage);
        }

        return to_route('products.show', $product)->with('success', 'La prenda se actualizó correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        DB::transaction(function () use ($product) {
            $product->inventoryMovements()->delete();
            $product->delete();
        });

        return to_route('products.index')->with('success', 'La prenda se eliminó.');
    }

    private function rules(?Product $product = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:180'],
            'category_id' => [
                'required',
                'integer',
                $product
                    ? Rule::exists('categories', 'id')->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $product->category_id))
                    : Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'size' => ['nullable', 'string', 'max:40'],
            'color' => ['nullable', 'string', 'max:80'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'sale_price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'received_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if (! $product) {
            $rules['stock'] = ['required', 'integer', 'min:0', 'max:1000000'];
        }

        return $rules;
    }

    private function newCode(): string
    {
        do {
            $code = 'PRD-'.Str::upper(Str::random(8));
        } while (Product::query()->where('code', $code)->exists());

        return $code;
    }
}
