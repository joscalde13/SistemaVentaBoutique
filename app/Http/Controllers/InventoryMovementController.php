<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryMovementController extends Controller
{
    public function index(): View
    {
        return view('inventory.index', [
            'movements' => InventoryMovement::query()
                ->with(['product', 'user', 'sale'])
                ->latest()
                ->paginate(20),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'stock']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', 'in:entry,withdrawal'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);
            $before = $product->stock;
            $quantity = (int) $data['quantity'];

            if ($data['type'] === 'withdrawal' && $quantity > $before) {
                throw ValidationException::withMessages(['quantity' => 'La cantidad no puede superar las existencias disponibles.']);
            }

            $after = $data['type'] === 'entry' ? $before + $quantity : $before - $quantity;
            $product->update(['stock' => $after]);

            InventoryMovement::create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'type' => $data['type'],
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'reason' => filled($data['reason'] ?? null) ? $data['reason'] : ($data['type'] === 'entry' ? 'Entrada de inventario' : 'Salida de inventario'),
            ]);
        });

        return to_route('inventory.index')->with('success', 'El movimiento de inventario se registró correctamente.');
    }

    public function destroy(InventoryMovement $movement): RedirectResponse
    {
        if ($movement->sale_id) {
            return to_route('inventory.index')->withErrors(['movement' => 'Este movimiento pertenece a una venta. Elimina o anula la venta desde el historial de ventas.']);
        }

        try {
            DB::transaction(function () use ($movement) {
                $movement = InventoryMovement::query()->lockForUpdate()->findOrFail($movement->id);
                $product = Product::query()->lockForUpdate()->findOrFail($movement->product_id);

                $adds = in_array($movement->type, ['entry', 'sale_return'], true);
                $stock = $adds ? $product->stock - $movement->quantity : $product->stock + $movement->quantity;

                if ($stock < 0) {
                    throw ValidationException::withMessages(['movement' => 'No se puede eliminar: la prenda ya no tiene suficientes existencias para revertir este movimiento.']);
                }

                $product->update(['stock' => $stock]);
                $movement->delete();
            }, 3);
        } catch (ValidationException $e) {
            return to_route('inventory.index')->withErrors($e->errors());
        }

        return to_route('inventory.index')->with('success', 'El movimiento se eliminó y las existencias volvieron a como estaban.');
    }
}
