<x-layouts.app :title="__('Reportes')">
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.18em] text-amber-700">Reportes</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900">Reporte mensual de ventas</h1>
                <p class="mt-1 text-sm text-zinc-500">Consulta lo vendido, invertido y ganado en cada mes.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.excel', ['month' => $month]) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Exportar Excel</a>
                <a href="{{ route('reports.pdf', ['month' => $month]) }}" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">Exportar PDF</a>
            </div>
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
            <label class="text-sm font-medium text-zinc-700">Mes
                <input type="month" name="month" value="{{ $month }}" max="{{ now()->format('Y-m') }}" class="mt-1 block rounded-xl border border-zinc-900 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-900/20">
            </label>
            <button class="rounded-xl bg-zinc-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-zinc-700">Consultar</button>
        </form>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm"><p class="text-sm text-zinc-500">Ventas realizadas</p><p class="mt-3 text-3xl font-semibold text-zinc-900">{{ number_format($salesCount) }}</p></article>
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm"><p class="text-sm text-zinc-500">Total vendido</p><p class="mt-3 text-3xl font-semibold text-zinc-900">{{ $currencySymbol }} {{ number_format((float) $revenue, 2) }}</p></article>
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm"><p class="text-sm text-zinc-500">Total invertido</p><p class="mt-3 text-3xl font-semibold text-amber-700">{{ $currencySymbol }} {{ number_format((float) $invested, 2) }}</p><p class="mt-1 text-xs text-zinc-400">Mercadería ingresada en el mes</p></article>
            <article class="rounded-2xl bg-zinc-900 p-5 shadow-sm"><p class="text-sm text-zinc-300">Total ganado</p><p class="mt-3 text-3xl font-semibold text-white">{{ $currencySymbol }} {{ number_format((float) $profit, 2) }}</p></article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-100 px-5 py-4"><h2 class="font-semibold text-zinc-900">Ventas de {{ $monthLabel }}</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500"><tr><th class="px-5 py-3">Recibo</th><th class="px-5 py-3">Fecha</th><th class="px-5 py-3">Prendas</th><th class="px-5 py-3">Pago</th><th class="px-5 py-3 text-right">Total</th><th class="px-5 py-3 text-right">Ganancia</th></tr></thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($sales as $sale)
                            <tr class="hover:bg-zinc-50/70">
                                <td class="px-5 py-4 font-semibold"><a href="{{ route('sales.show', $sale) }}" class="text-zinc-900 hover:underline">{{ $sale->number }}</a></td>
                                <td class="whitespace-nowrap px-5 py-4 text-zinc-600">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                <td class="max-w-72 px-5 py-4 text-zinc-600">{{ $sale->details->sum('quantity') }} u. <span class="block truncate text-xs text-zinc-400">{{ $sale->details->pluck('product_name')->join(', ') }}</span></td>
                                <td class="px-5 py-4 text-zinc-600">{{ $sale->payment_method ?: '—' }}</td>
                                <td class="px-5 py-4 text-right font-semibold text-zinc-900">{{ $currencySymbol }} {{ number_format((float) $sale->total, 2) }}</td>
                                <td class="px-5 py-4 text-right font-medium text-emerald-700">{{ $currencySymbol }} {{ number_format((float) $sale->profit_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-zinc-500">No hay ventas en este mes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <p class="text-xs text-zinc-400">No incluye ventas anuladas. La ganancia usa el costo de compra de cada venta.</p>
    </div>
</x-layouts.app>
