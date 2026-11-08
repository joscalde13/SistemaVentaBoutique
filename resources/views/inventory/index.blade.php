<x-layouts.app :title="__('Movimientos de inventario')">
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div><p class="text-sm font-medium uppercase tracking-[0.18em] text-amber-700">Control de existencias</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900">Movimientos de inventario</h1><p class="mt-1 text-sm text-zinc-500">Entradas, salidas, ventas y anulaciones quedan registradas.</p></div>
        @error('movement')<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>@enderror
        @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
        <section class="space-y-6">
            <form action="{{ route('inventory.store') }}" method="POST" class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">@csrf
                <div class="mb-5"><h2 class="font-semibold text-zinc-900">Registrar ajuste</h2><p class="mt-1 text-sm text-zinc-500">Registra una entrada o salida manual e indica el motivo.</p></div>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <label class="block text-sm font-medium text-zinc-700">Selecciona una Prenda *
                    <select name="product_id" required class="mt-1.5 w-full rounded-xl border border-zinc-900 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-zinc-900/20"><option value="">Selecciona</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }} ({{ $product->stock }} disp.)</option>@endforeach</select>
                </label>
                <label class="block text-sm font-medium text-zinc-700">Tipo de movimiento *
                    <select name="type" required class="mt-1.5 w-full rounded-xl border border-zinc-900 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-zinc-900/20"><option value="entry" @selected(old('type') === 'entry')>Entrada</option><option value="withdrawal" @selected(old('type') === 'withdrawal')>Salida / retiro</option></select>
                </label>
                <label class="block text-sm font-medium text-zinc-700">Cantidad *
                    <input type="number" name="quantity" min="1" max="1000000" required value="{{ old('quantity', 1) }}" class="mt-1.5 w-full rounded-xl border border-zinc-900 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-zinc-900/20">
                    @error('quantity')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
              
                </div>
                <div class="mt-5 flex justify-end"><button class="rounded-xl bg-zinc-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-zinc-700">Guardar movimiento</button></div>
            </form>

            <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <div class="border-b border-zinc-100 px-5 py-4"><h2 class="font-semibold text-zinc-900">Bitácora de inventario</h2></div>
                <div class="overflow-x-auto"><table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500"><tr><th class="px-5 py-3">Fecha / prenda</th><th class="px-5 py-3">Movimiento</th><th class="px-5 py-3">Existencias</th><th class="px-5 py-3">Motivo</th><th class="px-5 py-3">Usuario</th><th class="px-5 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($movements as $movement)
                            @php($positive = in_array($movement->type, ['entry', 'sale_return'], true))
                            <tr class="hover:bg-zinc-50/70">
                                <td class="whitespace-nowrap px-5 py-4"><p class="font-medium text-zinc-900">{{ $movement->product->name }}</p><p class="mt-1 text-xs text-zinc-500">{{ $movement->created_at->format('d/m/Y H:i') }}</p></td>
                                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $positive ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200' : 'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-200' }}">{{ $movement->type_label }}</span><p class="mt-1 text-sm font-semibold {{ $positive ? 'text-emerald-700' : 'text-red-600' }}">{{ $positive ? '+' : '−' }}{{ $movement->quantity }} u.</p></td>
                                <td class="whitespace-nowrap px-5 py-4 text-zinc-600">{{ $movement->stock_before }} → <strong class="text-zinc-900">{{ $movement->stock_after }}</strong></td>
                                <td class="max-w-56 px-5 py-4 text-zinc-600">{{ $movement->reason }} @if($movement->sale)<a href="{{ route('sales.show', $movement->sale) }}" class="mt-1 block text-xs text-amber-800 hover:underline">Ver recibo {{ $movement->sale->number }}</a>@endif</td>
                                <td class="px-5 py-4 text-zinc-600">{{ $movement->user->name }}</td>
                                <td class="px-5 py-4"><form method="POST" action="{{ route('inventory.destroy', $movement) }}" onsubmit="return confirm('¿Eliminar este movimiento? Las existencias de la prenda volverán a como estaban antes.')">@csrf @method('DELETE')<button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200 transition hover:bg-red-600 hover:text-white hover:ring-red-600"><svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.35 9m-4.78 0L9.26 9m9.97-3.21c.34.05.68.11 1.02.17m-1.02-.17-1.08 13.9a2.25 2.25 0 0 1-2.24 2.08H8.08a2.25 2.25 0 0 1-2.24-2.08L4.77 5.79m14.46 0a48.1 48.1 0 0 0-3.48-.4m-12 .57c.34-.06.68-.12 1.02-.17m0 0a48.1 48.1 0 0 1 3.48-.4m7.5 0v-.92c0-1.18-.91-2.16-2.09-2.2a51.96 51.96 0 0 0-3.32 0c-1.18.04-2.09 1.02-2.09 2.2v.92m7.5 0a48.67 48.67 0 0 0-7.5 0"/></svg>Eliminar</button></form></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-zinc-500">Aún no hay movimientos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
                <div class="border-t border-zinc-100 p-4">{{ $movements->links() }}</div>
            </section>
        </section>
    </div>
</x-layouts.app>
