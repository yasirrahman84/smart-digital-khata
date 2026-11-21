<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reports) {}

    public function receivables(Request $request)
    {
        $rows = $this->reports->receivables($request->user());

        return view('reports.receivables', [
            'rows' => $rows,
            'total' => round($rows->sum('balance'), 2),
        ]);
    }

    public function receivablesCsv(Request $request)
    {
        $rows = $this->reports->receivables($request->user());

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Customer', 'Phone', 'Balance (Rs)', 'Oldest due', 'Days overdue', 'Bucket']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row['customer']->name,
                    $row['customer']->phone,
                    number_format($row['balance'], 2, '.', ''),
                    $row['oldest_date']?->toDateString(),
                    $row['age_days'],
                    $row['bucket'],
                ]);
            }

            fclose($out);
        }, 'receivables-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function sales(Request $request)
    {
        $report = $this->reports->salesReport(
            $request->user(),
            $request->query('from'),
            $request->query('to'),
        );

        return view('reports.sales', $report);
    }

    public function salesCsv(Request $request)
    {
        $report = $this->reports->salesReport(
            $request->user(),
            $request->query('from'),
            $request->query('to'),
        );

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Invoice', 'Date', 'Customer', 'Payment', 'Subtotal (Rs)', 'Discount (Rs)', 'Total (Rs)', 'Status']);

            foreach ($report['sales'] as $sale) {
                fputcsv($out, [
                    $sale->invoice_no,
                    $sale->sale_date->format('Y-m-d H:i'),
                    $sale->customer?->name ?? 'Walk-in',
                    $sale->payment_method,
                    number_format((float) $sale->subtotal, 2, '.', ''),
                    number_format((float) $sale->discount, 2, '.', ''),
                    number_format((float) $sale->total, 2, '.', ''),
                    $sale->isVoided() ? 'void' : 'active',
                ]);
            }

            fclose($out);
        }, 'sales-'.$report['from'].'-to-'.$report['to'].'.csv', ['Content-Type' => 'text/csv']);
    }

    public function stock(Request $request)
    {
        $report = $this->reports->stockReport($request->user());

        return view('reports.stock', $report);
    }

    public function stockCsv(Request $request)
    {
        $report = $this->reports->stockReport($request->user());

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Product', 'SKU', 'Unit', 'Stock', 'Purchase price (Rs)', 'Sale price (Rs)', 'Stock value (Rs)', 'Low stock']);

            foreach ($report['products'] as $product) {
                fputcsv($out, [
                    $product->name,
                    $product->sku,
                    $product->unit,
                    number_format((float) $product->stock_qty, 3, '.', ''),
                    number_format((float) $product->purchase_price, 2, '.', ''),
                    number_format((float) $product->sale_price, 2, '.', ''),
                    number_format($product->stockValue(), 2, '.', ''),
                    $product->isLowStock() ? 'yes' : 'no',
                ]);
            }

            fclose($out);
        }, 'stock-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
