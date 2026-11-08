<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo {{ $sale->number }} | Margarita's</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .receipt {
                width: 100% !important;
                max-width: none !important;
                overflow: visible !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .receipt-content {
                padding: 14mm !important;
            }

            .receipt-table {
                min-width: 0 !important;
                table-layout: fixed;
            }

            .receipt-row,
            .receipt-summary,
            .receipt-footer {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-stone-100 px-4 py-8 text-stone-800">
    <main class="receipt mx-auto max-w-3xl overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-xl shadow-stone-200/60">
        <div class="no-print flex items-center justify-between gap-4 px-6 pt-6 sm:px-10">
            <a href="{{ route('sales.show', $sale) }}" class="text-sm font-medium text-stone-600 transition hover:text-stone-900">← Volver a la venta</a>
            <button onclick="window.print()" class="rounded-xl bg-stone-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-700">Imprimir / Guardar PDF</button>
        </div>

        <div class="receipt-content p-6 sm:p-10">
            <header class="flex flex-col justify-between gap-6 border-b border-stone-200 pb-7 sm:flex-row sm:items-center">
                <div class="flex items-center gap-4">
                    <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-stone-50 p-2 ring-1 ring-stone-200">
                        <img
                            src="{{ $business?->logo_path ? asset('storage/'.$business->logo_path) : asset('img/logo.jpg') }}"
                            alt="Logo de Margarita's"
                            class="max-h-full max-w-full object-contain"
                        >
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-rose-800">Boutique</p>
                        <h1 class="mt-1 font-serif text-3xl font-semibold tracking-tight text-stone-900">Margarita's</h1>
                        @if ($business?->address)
                            <p class="mt-2 text-sm text-stone-500">{{ $business->address }}</p>
                        @endif
                        @if ($business?->phone || $business?->email)
                            <p class="mt-1 text-sm text-stone-500">
                                @if ($business?->phone){{ $business->phone }}@endif
                                @if ($business?->phone && $business?->email)<span class="px-1">·</span>@endif
                                @if ($business?->email){{ $business->email }}@endif
                            </p>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl bg-stone-50 px-5 py-4 sm:min-w-52 sm:text-right">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500">Recibo de compra</p>
                    <p class="mt-1 text-lg font-bold text-stone-900">{{ $sale->number }}</p>
                    <p class="mt-2 text-sm text-stone-600">{{ $sale->created_at->format('d/m/Y') }}</p>
                    <p class="text-sm text-stone-500">{{ $sale->created_at->format('H:i') }}</p>
                </div>
            </header>

            @if ($sale->status === 'cancelled')
                <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-bold">Venta anulada</p>
                    @if ($sale->cancellation_reason)
                        <p class="mt-1">{{ $sale->cancellation_reason }}</p>
                    @endif
                </div>
            @endif

            <section class="mt-8">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-stone-900">Detalle de compra</h2>
                    <p class="mt-1 text-sm text-stone-500">{{ $sale->details->count() }} producto(s)</p>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-stone-200">
                    <table class="receipt-table w-full min-w-[600px] text-left text-sm">
                        <thead class="bg-stone-900 text-xs uppercase tracking-wider text-white">
                            <tr>
                                <th class="w-[34%] px-4 py-3.5 font-semibold">Producto</th>
                                <th class="w-[10%] px-2 py-3.5 text-center font-semibold">Cant.</th>
                                <th class="w-[19%] px-2 py-3.5 text-right font-semibold">Precio</th>
                                <th class="w-[16%] px-2 py-3.5 text-right font-semibold">Desc.</th>
                                <th class="w-[21%] px-4 py-3.5 text-right font-semibold">Importe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($sale->details as $detail)
                                <tr class="receipt-row">
                                    <td class="break-words px-4 py-4 font-medium text-stone-800">{{ $detail->product_name }}</td>
                                    <td class="px-2 py-4 text-center text-stone-600">{{ $detail->quantity }}</td>
                                    <td class="px-2 py-4 text-right text-stone-600">{{ $currencySymbol }} {{ number_format((float) $detail->unit_sale_price, 2) }}</td>
                                    <td class="px-2 py-4 text-right text-stone-600">{{ $currencySymbol }} {{ number_format((float) $detail->unit_discount, 2) }}</td>
                                    <td class="px-4 py-4 text-right font-semibold text-stone-900">{{ $currencySymbol }} {{ number_format((float) $detail->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="receipt-summary mt-7 flex justify-end">
                <div class="w-full rounded-2xl bg-stone-50 p-5 sm:max-w-sm">
                    <p class="flex justify-between gap-4 py-1.5 text-sm text-stone-600">
                        <span>Subtotal</span>
                        <span>{{ $currencySymbol }} {{ number_format((float) $sale->subtotal, 2) }}</span>
                    </p>
                    <p class="flex justify-between gap-4 py-1.5 text-sm text-stone-600">
                        <span>Descuento</span>
                        <span>− {{ $currencySymbol }} {{ number_format((float) $sale->discount_total, 2) }}</span>
                    </p>
                    <div class="my-2 border-t border-stone-200"></div>
                    <p class="flex items-baseline justify-between gap-4">
                        <span class="font-semibold text-stone-800">Total pagado</span>
                        <span class="text-2xl font-bold tracking-tight text-stone-900">{{ $currencySymbol }} {{ number_format((float) $sale->total, 2) }}</span>
                    </p>
                    @if ($sale->payment_method)
                        <p class="mt-3 flex justify-between gap-4 border-t border-stone-200 pt-3 text-sm text-stone-500">
                            <span>Forma de pago</span>
                            <span class="font-medium text-stone-700">{{ $sale->payment_method }}</span>
                        </p>
                    @endif
                </div>
            </section>

            <footer class="receipt-footer mt-9 border-t border-stone-200 pt-6 text-center">
                <p class="font-serif text-xl font-semibold text-stone-900">¡Gracias por elegir Margarita's!</p>
                <p class="mt-1 text-sm text-stone-500">Esperamos verte pronto.</p>
                <p class="mt-4 text-xs text-stone-400">Atendió: {{ $sale->user->name }}</p>
            </footer>
        </div>
    </main>
</body>
</html>
