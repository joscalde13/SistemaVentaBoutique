<x-layouts.app :title="__('Dashboard')">
    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.18em] text-amber-700">Resumen del negocio</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900">{{ $business?->business_name ?? 'Mi tienda' }}</h1>
                <p class="mt-1 text-sm text-zinc-500">Esto es lo que está pasando en tu tienda hoy.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('products.create') }}" class="rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-700">Agregar prenda</a>
                <a href="{{ route('sales.create') }}" class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-800 hover:bg-zinc-50">Registrar venta</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>Cerrar sesión</button>
                </form>
            </div>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Ventas realizadas', number_format($totalSales), 'Operaciones completadas'],
                ['Prendas disponibles', number_format($availableGarments), 'Unidades en inventario'],
                ['Prendas vendidas', number_format($soldGarments), 'Unidades vendidas'],
                ['Categorías activas', number_format($categoryCount), 'Divisiones del catálogo'],
            ] as [$label, $value, $hint])
                <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-zinc-500">{{ $label }}</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900">{{ $value }}</p>
                    <p class="mt-2 text-xs text-zinc-400">{{ $hint }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            @foreach ([
                ['Inversión realizada', $inventoryInvestment, 'Costo de compra de todo lo que has ingresado, aunque ya se haya vendido'],
                ['Ingresos por ventas', $salesRevenue, 'Total cobrado en ventas no anuladas'],
                ['Ganancia bruta', $grossProfit, 'Ingresos menos costo histórico de prendas'],
            ] as [$label, $value, $hint])
                <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-zinc-500">{{ $label }}</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-zinc-900">{{ $currencySymbol }} {{ number_format((float) $value, 2) }}</p>
                    <p class="mt-2 text-xs text-zinc-400">{{ $hint }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-5">
            <article class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm xl:col-span-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold text-zinc-900">Ventas de los últimos 7 días</h2>
                        <p class="mt-1 text-sm text-zinc-500">Ingresos registrados por día</p>
                    </div>
                    
                </div>
                <div class="mt-8 grid h-48 grid-cols-7 items-end gap-3">
                    @foreach ($dailySales as $day)
                        <div class="flex h-full flex-col items-center justify-end gap-2">
                            <span class="text-[11px] text-zinc-500">{{ $currencySymbol }}{{ number_format($day['amount'], 0) }}</span>
                            <div class="w-full rounded-t-lg bg-amber-500" style="height: {{ max(4, $day['amount'] / $maxSales * 100) }}%"></div>
                            <span class="text-xs text-zinc-500">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm xl:col-span-2">
                <div>
                    <h2 class="font-semibold text-zinc-900">Ganancia por mes</h2>
                    <p class="mt-1 text-sm text-zinc-500">Ganancia bruta en los últimos 6 meses</p>
                </div>
                <div class="mt-5 space-y-4">
                    @foreach ($profitsByMonth as $month)
                        <div>
                            <div class="mb-1.5 flex justify-between text-xs">
                                <span class="text-zinc-600">{{ $month['label'] }}</span>
                                <span class="font-medium text-zinc-800">{{ $currencySymbol }} {{ number_format($month['amount'], 2) }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-zinc-100">
                                <div class="h-full rounded-full bg-zinc-800" style="width: {{ min(100, $month['amount'] / $maxProfit * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-5 border-t border-zinc-100 pt-4 text-sm text-zinc-500">Ganancia de este mes <strong class="float-right text-zinc-900">{{ $currencySymbol }} {{ number_format((float) $monthlyProfit, 2) }}</strong></p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-5">
           

            <article class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm xl:col-span-3">
                <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-5">
                    <div>
                        <h2 class="font-semibold text-zinc-900">Actividad reciente</h2>
                        <p class="mt-1 text-sm text-zinc-500">Últimas ventas completadas</p>
                    </div>
                    <a href="{{ route('sales.index') }}" class="text-sm font-medium text-amber-800 hover:text-amber-900">Ver historial</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500">
                            <tr><th class="px-6 py-3">Venta</th><th class="px-6 py-3">Prendas</th><th class="px-6 py-3">Fecha</th><th class="px-6 py-3 text-right">Total</th></tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($recentSales as $sale)
                                <tr>
                                    <td class="px-6 py-4"><a class="font-medium text-zinc-900 hover:underline" href="{{ route('sales.show', $sale) }}">{{ $sale->number }}</a><p class="mt-1 text-xs text-zinc-500">{{ $sale->user->name }}</p></td>
                                    <td class="px-6 py-4 text-zinc-600">{{ $sale->details->sum('quantity') }} unidad(es)</td>
                                    <td class="px-6 py-4 text-zinc-600">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-zinc-900">{{ $currencySymbol }} {{ number_format((float) $sale->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-12 text-center text-zinc-500">Todavía no hay ventas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </div>
</x-layouts.app>
