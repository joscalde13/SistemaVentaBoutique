<x-layouts.app :title="$product->name">
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4"><div><a href="{{ route('products.index') }}" class="text-sm font-medium text-amber-800 hover:underline">← Inventario</a><h1 class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900">{{ $product->name }}</h1></div><a href="{{ route('products.edit', $product) }}" class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-800 hover:bg-zinc-50">Editar prenda</a></div>
        @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
        <section class="grid gap-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm md:grid-cols-[220px_1fr]">
            @if ($product->image_path)<img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="h-64 w-full rounded-xl object-cover">@else<div class="flex h-64 items-center justify-center rounded-xl bg-amber-50 text-5xl font-semibold text-amber-800">{{ mb_substr($product->name, 0, 1) }}</div>@endif
            <div>
                <div class="flex flex-wrap gap-2"><span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700">{{ $product->category->name }}</span><span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">{{ $product->status }}</span></div>
                <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                    @foreach (['Talla' => $product->size ?: '—', 'Color' => $product->color ?: '—', 'Precio de compra' => $currencySymbol.' '.number_format((float) $product->purchase_price, 2), 'Precio de venta' => $currencySymbol.' '.number_format((float) $product->sale_price, 2), 'Descuento máximo' => $currencySymbol.' '.number_format((float) $product->max_discount, 2), 'Existencias' => $product->stock, 'Ganancia esperada' => $currencySymbol.' '.number_format((float) $product->sale_price - (float) $product->purchase_price, 2), 'Ganancia mínima con descuento' => $currencySymbol.' '.number_format((float) $product->sale_price - (float) $product->max_discount - (float) $product->purchase_price, 2), 'Fecha de ingreso' => $product->received_at->format('d/m/Y')] as $label => $value)
                        <div><dt class="text-xs uppercase tracking-wide text-zinc-400">{{ $label }}</dt><dd class="mt-1 font-medium text-zinc-800">{{ $value }}</dd></div>
                    @endforeach
                </dl>
                @if ($product->description)<p class="mt-6 border-t border-zinc-100 pt-5 text-sm leading-6 text-zinc-600">{{ $product->description }}</p>@endif
            </div>
        </section>
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-100 px-6 py-4"><h2 class="font-semibold text-zinc-900">Movimientos recientes</h2></div>
            <div class="divide-y divide-zinc-100">
                @forelse ($product->inventoryMovements as $movement)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 text-sm"><div><p class="font-medium text-zinc-800">{{ $movement->type_label }} · {{ $movement->reason }}</p><p class="mt-1 text-xs text-zinc-500">{{ $movement->user->name }} · {{ $movement->created_at->format('d/m/Y H:i') }}</p></div><p class="text-zinc-700">{{ $movement->stock_before }} → <strong>{{ $movement->stock_after }}</strong> ({{ $movement->quantity }} u.)</p></div>
                @empty
                    <p class="px-6 py-8 text-center text-sm text-zinc-500">Sin movimientos registrados.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
