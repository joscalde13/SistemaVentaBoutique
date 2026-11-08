<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'month' => ['nullable', 'date_format:Y-m'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $monthStart = isset($filters['month']) ? Carbon::parse($filters['month'].'-01')->startOfMonth() : null;

        $firstSale = Sale::query()->oldest('created_at')->value('created_at');
        $monthOptions = [];
        if ($firstSale) {
            $firstMonth = Carbon::parse($firstSale)->startOfMonth();
            $lastMonth = Carbon::now()->startOfMonth();

            for ($month = $lastMonth->copy(); $month->greaterThanOrEqualTo($firstMonth); $month->subMonth()) {
                $monthOptions[] = [
                    'value' => $month->format('Y-m'),
                    'label' => $month->locale('es')->translatedFormat('F Y'),
                ];
            }
        }

        $sales = Sale::query()
            ->with(['user', 'details'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search')->toString().'%';
                $query->where(fn ($inner) => $inner
                    ->where('number', 'like', $search)
                    ->orWhereHas('details', fn ($details) => $details
                        ->where('product_name', 'like', $search)
                        ->orWhere('product_code', 'like', $search)));
            })
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $filters['to']))
            ->when($monthStart, fn ($query) => $query->whereBetween('created_at', [$monthStart, $monthStart->copy()->endOfMonth()]))
            ->when($request->filled('category_id'), fn ($query) => $query->whereHas('details', fn ($details) => $details->where('category_id', $filters['category_id'])))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
            'categories' => Category::query()->orderBy('name')->get(),
            'monthOptions' => $monthOptions,
        ]);
    }

    public function create(): View
    {
        return view('sales.create', [
            'business' => BusinessSetting::query()->first(),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $search = trim($request->string('search')->toString());

        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        return response()->json(
            Product::query()
                ->with('category:id,name')
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%'))
                ->orderBy('name')
                ->limit(10)
                ->get(['id', 'code', 'name', 'category_id', 'size', 'color', 'purchase_price', 'sale_price', 'max_discount', 'stock'])
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'category' => $product->category->name,
                    'size' => $product->size,
                    'color' => $product->color,
                    'purchase_price' => $product->purchase_price,
                    'sale_price' => $product->sale_price,
                    'max_discount' => $product->max_discount,
                    'stock' => $product->stock,
                ])
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.discount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'payment_method' => ['nullable', 'string', 'max:80'],
        ]);

        $sale = DB::transaction(function () use ($data, $request) {
            $ids = collect($data['items'])->pluck('product_id')->map(fn ($id) => (int) $id)->sort()->values();
            $products = Product::query()
                ->with('category')
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $ids->count()) {
                throw ValidationException::withMessages(['items' => 'Una o más prendas ya no están disponibles.']);
            }

            $subtotal = 0;
            $discountTotal = 0;
            $total = 0;
            $costTotal = 0;
            $lines = [];

            foreach ($data['items'] as $item) {
                $product = $products->get((int) $item['product_id']);
                $quantity = (int) $item['quantity'];
                $discountCents = $this->cents($item['discount']);
                $priceCents = $this->cents($product->sale_price);
                $costCents = $this->cents($product->purchase_price);
                $maximumDiscountCents = $this->cents($product->max_discount);

                if (! $product->is_active || $product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Existencias insuficientes para {$product->name}. Disponibles: {$product->stock}.",
                    ]);
                }

                if ($discountCents > $maximumDiscountCents) {
                    throw ValidationException::withMessages([
                        'items' => "El descuento para {$product->name} supera el máximo permitido.",
                    ]);
                }

                $lineSubtotal = $priceCents * $quantity;
                $lineDiscount = $discountCents * $quantity;
                $lineTotal = $lineSubtotal - $lineDiscount;
                $lineCost = $costCents * $quantity;

                $subtotal += $lineSubtotal;
                $discountTotal += $lineDiscount;
                $total += $lineTotal;
                $costTotal += $lineCost;
                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_discount' => $discountCents,
                    'unit_sale_price' => $priceCents,
                    'unit_purchase_price' => $costCents,
                    'line_total' => $lineTotal,
                    'line_cost' => $lineCost,
                    'line_profit' => $lineTotal - $lineCost,
                ];
            }

            $sale = Sale::create([
                'number' => $this->newSaleNumber(),
                'user_id' => $request->user()->id,
                'subtotal' => $this->decimal($subtotal),
                'discount_total' => $this->decimal($discountTotal),
                'total' => $this->decimal($total),
                'cost_total' => $this->decimal($costTotal),
                'profit_total' => $this->decimal($total - $costTotal),
                'payment_method' => $data['payment_method'] ?? null,
                'status' => 'completed',
            ]);

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];
                $before = $product->stock;
                $after = $before - $line['quantity'];
                $product->update(['stock' => $after]);

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'category_name' => $product->category->name,
                    'quantity' => $line['quantity'],
                    'unit_purchase_price' => $this->decimal($line['unit_purchase_price']),
                    'unit_sale_price' => $this->decimal($line['unit_sale_price']),
                    'unit_discount' => $this->decimal($line['unit_discount']),
                    'max_discount_snapshot' => $product->max_discount,
                    'line_total' => $this->decimal($line['line_total']),
                    'line_cost' => $this->decimal($line['line_cost']),
                    'line_profit' => $this->decimal($line['line_profit']),
                ]);

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'sale_id' => $sale->id,
                    'type' => 'sale',
                    'quantity' => $line['quantity'],
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'reason' => 'Venta realizada al cliente',
                ]);
            }

            return $sale;
        }, 3);

        return to_route('sales.receipt', $sale)->with('success', 'La venta se registró correctamente.');
    }

    public function show(Sale $sale): View
    {
        $sale->load(['details', 'user', 'cancelledBy']);

        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale): View
    {
        $sale->load(['details', 'user']);

        return view('sales.receipt', [
            'sale' => $sale,
            'business' => BusinessSetting::query()->first(),
        ]);
    }

    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $request, $sale) {
            $sale = Sale::query()->lockForUpdate()->findOrFail($sale->id);

            if ($sale->status !== 'completed') {
                throw ValidationException::withMessages(['sale' => 'Esta venta ya fue anulada.']);
            }

            $details = $sale->details()->orderBy('product_id')->get();
            $products = Product::query()
                ->whereIn('id', $details->pluck('product_id')->filter())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($details as $detail) {
                $product = $products->get($detail->product_id);

                if (! $product) {
                    continue;
                }

                $before = $product->stock;
                $after = $before + $detail->quantity;
                $product->update(['stock' => $after]);

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'sale_id' => $sale->id,
                    'type' => 'sale_return',
                    'quantity' => $detail->quantity,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'reason' => 'Venta anulada. Motivo: '.$data['reason'],
                ]);
            }

            $sale->update([
                'status' => 'cancelled',
                'cancellation_reason' => $data['reason'],
                'cancelled_by' => $request->user()->id,
                'cancelled_at' => now(),
            ]);
        }, 3);

        return to_route('sales.show', $sale)->with('success', 'La venta se anuló y las existencias fueron restauradas.');
    }

    public function destroy(Request $request, Sale $sale): RedirectResponse
    {
        DB::transaction(function () use ($request, $sale) {
            $sale = Sale::query()->lockForUpdate()->findOrFail($sale->id);

            if ($sale->status === 'completed') {
                $details = $sale->details()->orderBy('product_id')->get();
                $products = Product::query()
                    ->whereIn('id', $details->pluck('product_id')->filter())
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($details as $detail) {
                    $product = $products->get($detail->product_id);

                    if (! $product) {
                        continue;
                    }

                    $before = $product->stock;
                    $after = $before + $detail->quantity;
                    $product->update(['stock' => $after]);

                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'user_id' => $request->user()->id,
                        'type' => 'sale_return',
                        'quantity' => $detail->quantity,
                        'stock_before' => $before,
                        'stock_after' => $after,
                        'reason' => 'Venta eliminada del historial',
                    ]);
                }
            }

            InventoryMovement::query()->where('sale_id', $sale->id)->update(['sale_id' => null]);
            $sale->details()->delete();
            $sale->delete();
        }, 3);

        return to_route('sales.index')->with('success', 'La venta se eliminó del historial.');
    }

    private function cents(string|int|float $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private function decimal(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    private function newSaleNumber(): string
    {
        return 'REC-'.str_pad((string) ((int) Sale::query()->max('id') + 1), 5, '0', STR_PAD_LEFT);
    }
}
