<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\InventoryMovement;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('reports.index', $this->monthlyData($request));
    }

    public function pdf(Request $request)
    {
        $data = $this->monthlyData($request);

        return Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4')
            ->download('reporte-ventas-'.$data['month'].'.pdf');
    }

    public function excel(Request $request): StreamedResponse
    {
        $data = $this->monthlyData($request);
        $symbol = $data['currencySymbol'];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Reporte mensual');
        $sheet->setCellValue('A1', 'Reporte de ventas - '.$data['monthLabel']);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $summary = [
            ['Ventas realizadas', $data['salesCount']],
            ['Total vendido', (float) $data['revenue']],
            ['Total invertido', (float) $data['invested']],
            ['Total ganado', (float) $data['profit']],
        ];
        foreach ($summary as $i => [$label, $value]) {
            $row = 3 + $i;
            $sheet->setCellValue("A{$row}", $label)->setCellValue("B{$row}", $value);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            if ($i > 0) {
                $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('"'.$symbol.'" #,##0.00');
            }
        }

        $headerRow = 8;
        foreach (['Recibo', 'Fecha', 'Prendas', 'Forma de pago', 'Descuento', 'Total', 'Ganancia'] as $i => $title) {
            $sheet->setCellValue([$i + 1, $headerRow], $title);
        }
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('18181B');

        $row = $headerRow + 1;
        foreach ($data['sales'] as $sale) {
            $sheet->fromArray([
                $sale->number,
                $sale->created_at->format('d/m/Y H:i'),
                $sale->details->pluck('product_name')->join(', '),
                $sale->payment_method ?: 'No especificada',
                (float) $sale->discount_total,
                (float) $sale->total,
                (float) $sale->profit_total,
            ], null, "A{$row}");
            $row++;
        }
        if ($row > $headerRow + 1) {
            $sheet->getStyle('E'.($headerRow + 1).":G{$row}")->getNumberFormat()->setFormatCode('"'.$symbol.'" #,##0.00');
        }
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'reporte-ventas-'.$data['month'].'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function monthlyData(Request $request): array
    {
        $validated = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $start = Carbon::createFromFormat('Y-m-d', ($validated['month'] ?? today()->format('Y-m')).'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $completed = Sale::query()->where('status', 'completed')->whereBetween('created_at', [$start, $end]);

        $invested = InventoryMovement::query()
            ->where('inventory_movements.type', 'entry')
            ->whereBetween('inventory_movements.created_at', [$start, $end])
            ->join('products', 'products.id', '=', 'inventory_movements.product_id')
            ->selectRaw('COALESCE(SUM(products.purchase_price * inventory_movements.quantity), 0) as total')
            ->value('total');

        return [
            'month' => $start->format('Y-m'),
            'monthLabel' => ucfirst($start->copy()->locale('es')->translatedFormat('F Y')),
            'sales' => Sale::query()->with('details')->where('status', 'completed')
                ->whereBetween('created_at', [$start, $end])->orderBy('created_at')->get(),
            'salesCount' => (clone $completed)->count(),
            'revenue' => (clone $completed)->sum('total'),
            'invested' => $invested,
            'profit' => (clone $completed)->sum('profit_total'),
            'currencySymbol' => BusinessSetting::query()->value('currency_symbol') ?? 'Q',
        ];
    }
}
