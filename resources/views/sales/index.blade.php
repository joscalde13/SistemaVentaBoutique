<x-layouts.app :title="__('Historial de ventas')">
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-sm font-medium uppercase tracking-[0.18em] text-amber-700 dark:text-amber-400">Operaciones</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">Historial de ventas</h1><p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Ventas, descuentos y ganancias históricas.</p></div><a href="{{ route('sales.create') }}" class="rounded-xl bg-zinc-900 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600">+ Registrar venta</a></div>
        @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ session('success') }}</div>@endif
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-zinc-100 p-4 dark:border-zinc-700">
                <input name="search" value="{{ request('search') }}" placeholder="producto" class="rounded-xl border border-zinc-900 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-zinc-900/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-500 dark:focus:ring-zinc-400/30">
                <label class="flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    <select name="month" class="rounded-xl border border-zinc-900 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-900/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:ring-zinc-400/30">
                        <option value="">Seleccionar mes</option>
                        @foreach ($monthOptions as $month)
                            <option value="{{ $month['value'] }}" @selected(request('month') === $month['value'])>{{ ucfirst($month['label']) }}</option>
                        @endforeach
                    </select>
                </label>
                <select name="category_id" class="min-w-64 rounded-xl border border-zinc-900 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-zinc-900/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:ring-zinc-400/30"><option value="">Todas las categorías</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select>
                <button class="ml-auto rounded-xl bg-zinc-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600">Filtrar</button>
            </form>
            <div class="overflow-x-auto"><table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400"><tr><th class="px-5 py-3">Venta</th><th class="px-5 py-3">Productos</th><th class="px-5 py-3">Fecha / usuario</th><th class="px-5 py-3">Descuento</th><th class="px-5 py-3">Ganancia</th><th class="px-5 py-3 text-right">Total</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3"></th></tr></thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @forelse ($sales as $sale)
                        <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-700/40">
                            <td class="px-5 py-4"><a href="{{ route('sales.show', $sale) }}" class="font-semibold text-zinc-900 hover:underline dark:text-zinc-100">{{ $sale->number }}</a></td>
                            <td class="max-w-64 px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $sale->details->sum('quantity') }} unidad(es)<p class="mt-1 truncate text-xs text-zinc-400 dark:text-zinc-500">{{ $sale->details->pluck('product_name')->join(', ') }}</p></td>
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $sale->created_at->format('d/m/Y H:i') }}<p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ $sale->user->name }}</p></td>
                            <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $currencySymbol }} {{ number_format((float) $sale->discount_total, 2) }}</td>
                            <td class="px-5 py-4 font-medium text-emerald-700">{{ $currencySymbol }} {{ number_format((float) $sale->profit_total, 2) }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-zinc-900">{{ $currencySymbol }} {{ number_format((float) $sale->total, 2) }}</td>
                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $sale->status === 'completed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300' }}">{{ $sale->status === 'completed' ? 'Completada' : 'Anulada' }}</span></td>
                            <td class="px-5 py-4"><form method="POST" action="{{ route('sales.destroy', $sale) }}" onsubmit="return confirm('¿Eliminar esta venta? Se borrará del historial{{ $sale->status === 'completed' ? ' y las prendas volverán al inventario' : '' }}.')">@csrf @method('DELETE')<button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200 transition hover:bg-red-600 hover:text-white hover:ring-red-600 dark:bg-red-950 dark:text-red-300 dark:ring-red-900 dark:hover:bg-red-600 dark:hover:text-white dark:hover:ring-red-600"><svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.35 9m-4.78 0L9.26 9m9.97-3.21c.34.05.68.11 1.02.17m-1.02-.17-1.08 13.9a2.25 2.25 0 0 1-2.24 2.08H8.08a2.25 2.25 0 0 1-2.24-2.08L4.77 5.79m14.46 0a48.1 48.1 0 0 0-3.48-.4m-12 .57c.34-.06.68-.12 1.02-.17m0 0a48.1 48.1 0 0 1 3.48-.4m7.5 0v-.92c0-1.18-.91-2.16-2.09-2.2a51.96 51.96 0 0 0-3.32 0c-1.18.04-2.09 1.02-2.09 2.2v.92m7.5 0a48.67 48.67 0 0 0-7.5 0"/></svg>Eliminar</button></form></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-12 text-center text-zinc-500 dark:text-zinc-400">No hay ventas que coincidan con los filtros.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
            <div class="border-t border-zinc-100 p-4 dark:border-zinc-700">{{ $sales->links() }}</div>
        </section>
    </div>
</x-layouts.app>
