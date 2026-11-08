<x-layouts.app :title="$sale->number">
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><a href="{{ route('sales.index') }}" class="text-sm font-medium text-amber-800 hover:underline">← Historial de ventas</a><h1 class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900">{{ $sale->number }}</h1><p class="mt-1 text-sm text-zinc-500">{{ $sale->created_at->format('d/m/Y H:i') }} · Registrada por {{ $sale->user->name }}</p></div><a href="{{ route('sales.receipt', $sale) }}" class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-800 hover:bg-zinc-50">Ver comprobante</a></div>
        @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
        @if ($sale->status === 'cancelled')
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><strong>Venta anulada</strong><p class="mt-1">Motivo: {{ $sale->cancellation_reason }}</p><p class="mt-1 text-xs">Anulada el {{ $sale->cancelled_at?->format('d/m/Y H:i') }}{{ $sale->cancelledBy ? ' por '.$sale->cancelledBy->name : '' }}</p></div>
        @endif
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500"><tr><th class="px-5 py-3">Prenda</th><th class="px-5 py-3">Cantidad</th><th class="px-5 py-3">Precio</th><th class="px-5 py-3">Descuento/u.</th><th class="px-5 py-3">Costo histórico</th><th class="px-5 py-3 text-right">Total</th></tr></thead>
                <tbody class="divide-y divide-zinc-100">@foreach ($sale->details as $detail)<tr><td class="px-5 py-4"><p class="font-medium text-zinc-900">{{ $detail->product_name }}</p><p class="mt-1 text-xs text-zinc-500">{{ $detail->category_name }}</p></td><td class="px-5 py-4 text-zinc-600">{{ $detail->quantity }}</td><td class="px-5 py-4 text-zinc-600">{{ $currencySymbol }} {{ number_format((float) $detail->unit_sale_price, 2) }}</td><td class="px-5 py-4 text-zinc-600">{{ $currencySymbol }} {{ number_format((float) $detail->unit_discount, 2) }}</td><td class="px-5 py-4 text-zinc-600">{{ $currencySymbol }} {{ number_format((float) $detail->unit_purchase_price, 2) }}</td><td class="px-5 py-4 text-right font-medium text-zinc-900">{{ $currencySymbol }} {{ number_format((float) $detail->line_total, 2) }}</td></tr>@endforeach</tbody>
            </table></div>
            <div class="ml-auto max-w-sm space-y-2 border-t border-zinc-100 p-5 text-sm">
                <p class="flex justify-between text-zinc-600"><span>Subtotal</span><span>{{ $currencySymbol }} {{ number_format((float) $sale->subtotal, 2) }}</span></p>
                <p class="flex justify-between text-zinc-600"><span>Descuento</span><span>− {{ $currencySymbol }} {{ number_format((float) $sale->discount_total, 2) }}</span></p>
                <p class="flex justify-between border-t border-zinc-100 pt-3 font-semibold text-zinc-900"><span>Total</span><span>{{ $currencySymbol }} {{ number_format((float) $sale->total, 2) }}</span></p>
                <p class="flex justify-between text-zinc-600"><span>Costo de prendas</span><span>{{ $currencySymbol }} {{ number_format((float) $sale->cost_total, 2) }}</span></p>
                <p class="flex justify-between font-semibold text-emerald-700"><span>Ganancia bruta</span><span>{{ $currencySymbol }} {{ number_format((float) $sale->profit_total, 2) }}</span></p>
                @if ($sale->payment_method)<p class="flex justify-between border-t border-zinc-100 pt-3 text-zinc-600"><span>Forma de pago</span><span>{{ $sale->payment_method }}</span></p>@endif
            </div>
        </section>
        @if ($sale->status === 'completed')
            <form action="{{ route('sales.cancel', $sale) }}" method="POST" class="rounded-2xl border border-red-200 bg-white p-5" onsubmit="return confirm('¿Confirmas la anulación? Se restaurarán las existencias y el historial se conservará.')">@csrf
                <h2 class="font-semibold text-zinc-900">Anular venta</h2><p class="mt-1 text-sm text-zinc-500">La anulación no borra el registro original y restaurará las existencias.</p>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row"><input name="reason" required minlength="5" maxlength="1000" placeholder="Motivo de la anulación" class="min-w-0 flex-1 rounded-xl border border-zinc-900 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-zinc-900/20"><button class="rounded-xl bg-red-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-800">Anular venta</button></div>
                @error('reason')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </form>
        @endif
    </div>
</x-layouts.app>
