<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today = today();
        $weekStart = $today->copy()->subDays(6);
        $monthStart = $today->copy()->startOfMonth();

        $salesByDay = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$weekStart->startOfDay(), $today->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as day, SUM(total) as amount')
            ->groupBy('day')
            ->pluck('amount', 'day');

        $dailySales = collect(range(6, 0))->map(function (int $daysAgo) use ($today, $salesByDay) {
            $date = $today->copy()->subDays($daysAgo);

            return [
                'label' => $date->copy()->locale('es')->translatedFormat('D'),
                'date' => $date->toDateString(),
                'amount' => (float) ($salesByDay[$date->toDateString()] ?? 0),
            ];
        });

        $monthlyProfit = Sale::query()
            ->where('status', 'completed')
            ->where('created_at', '>=', $monthStart)
            ->sum('profit_total');

        $monthlySales = Sale::query()
            ->where('status', 'completed')
            ->where('created_at', '>=', $monthStart)
            ->sum('total');

        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthlyProfitChart = Sale::query()
            ->where('status', 'completed')
            ->where('created_at', '>=', $today->copy()->subMonths(5)->startOfMonth())
            ->selectRaw("$monthExpression as month, SUM(profit_total) as amount")
            ->groupBy('month')
            ->pluck('amount', 'month');

        $profitsByMonth = collect(range(5, 0))->map(function (int $monthsAgo) use ($today, $monthlyProfitChart) {
            $date = $today->copy()->subMonths($monthsAgo);
            $key = $date->format('Y-m');

            return [
                'label' => $date->copy()->locale('es')->translatedFormat('M'),
                'amount' => (float) ($monthlyProfitChart[$key] ?? 0),
            ];
        });

        $maxSales = max(1, (float) $dailySales->max('amount'));
        $maxProfit = max(1, (float) $profitsByMonth->max('amount'));

        $categorySales = SaleDetail::query()
            ->join('sales', 'sales.id', '=', 'sale_details.sale_id')
            ->where('sales.status', 'completed')
            ->select('sale_details.category_name', DB::raw('SUM(sale_details.quantity) as quantity'))
            ->groupBy('sale_details.category_name')
            ->orderByDesc('quantity')
            ->limit(6)
            ->get();

        $availableGarments = Product::query()->where('is_active', true)->sum('stock');

        return view('dashboard', [
            'totalSales' => Sale::query()->where('status', 'completed')->count(),
            'availableGarments' => $availableGarments,
            'soldGarments' => SaleDetail::query()
                ->whereHas('sale', fn ($query) => $query->where('status', 'completed'))
                ->sum('quantity'),
            'inventoryInvestment' => InventoryMovement::query()
                ->where('inventory_movements.type', 'entry')
                ->join('products', 'products.id', '=', 'inventory_movements.product_id')
                ->selectRaw('COALESCE(SUM(products.purchase_price * inventory_movements.quantity), 0) as total')
                ->value('total'),
            'salesRevenue' => Sale::query()->where('status', 'completed')->sum('total'),
            'grossProfit' => Sale::query()->where('status', 'completed')->sum('profit_total'),
            'categoryCount' => Category::query()->where('is_active', true)->count(),
            'monthlySales' => $monthlySales,
            'monthlyProfit' => $monthlyProfit,
            'dailySales' => $dailySales,
            'profitsByMonth' => $profitsByMonth,
            'categorySales' => $categorySales,
            'maxSales' => $maxSales,
            'maxProfit' => $maxProfit,
            'recentSales' => Sale::query()->with(['details', 'user'])
                ->where('status', 'completed')
                ->latest()
                ->limit(6)
                ->get(),
            'business' => BusinessSetting::query()->first(),
        ]);
    }
}
