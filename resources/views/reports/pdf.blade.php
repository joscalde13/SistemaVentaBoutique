<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #18181b; }
    h1 { font-size: 20px; margin: 0; }
    .muted { color: #71717a; }
    .cards { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 16px -8px; }
    .cards td { border: 1px solid #e4e4e7; padding: 10px; width: 25%; }
    .cards .dark { background: #18181b; color: #fff; }
    .cards b { display: block; font-size: 15px; margin-top: 4px; }
    table.list { width: 100%; border-collapse: collapse; }
    table.list th { background: #18181b; color: #fff; text-align: left; padding: 6px; font-size: 10px; }
    table.list td { padding: 6px; border-bottom: 1px solid #e4e4e7; }
    .r { text-align: right; }
</style>
</head>
<body>
    <h1>Reporte de ventas</h1>
    <p class="muted">{{ $monthLabel }}</p>
    <table class="cards"><tr>
        <td>Ventas realizadas<b>{{ $salesCount }}</b></td>
        <td>Total vendido<b>{{ $currencySymbol }} {{ number_format((float) $revenue, 2) }}</b></td>
        <td>Total invertido<b>{{ $currencySymbol }} {{ number_format((float) $invested, 2) }}</b></td>
        <td class="dark">Total ganado<b>{{ $currencySymbol }} {{ number_format((float) $profit, 2) }}</b></td>
    </tr></table>
    <table class="list">
        <thead><tr><th>Recibo</th><th>Fecha</th><th>Prendas</th><th>Pago</th><th class="r">Total</th><th class="r">Ganancia</th></tr></thead>
        <tbody>
        @forelse ($sales as $sale)
            <tr>
                <td>{{ $sale->number }}</td>
                <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $sale->details->pluck('product_name')->join(', ') }}</td>
                <td>{{ $sale->payment_method ?: '—' }}</td>
                <td class="r">{{ $currencySymbol }} {{ number_format((float) $sale->total, 2) }}</td>
                <td class="r">{{ $currencySymbol }} {{ number_format((float) $sale->profit_total, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">No hay ventas en este mes.</td></tr>
        @endforelse
        </tbody>
    </table>
    <p class="muted" style="margin-top:12px">Generado el {{ now()->format('d/m/Y H:i') }}. No incluye ventas anuladas.</p>
</body>
</html>
