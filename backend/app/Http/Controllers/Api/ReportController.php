<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Laporan stok saat ini.
     */
    public function stock(Request $request)
    {
        $products = Product::query()
            ->with([
                'category:id,name',
                'brand:id,name',
            ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('type_model', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->brand_id, function ($query, $brandId) {
                $query->where('brand_id', $brandId);
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'out_of_stock') {
                    $query->where('stock', 0);
                }

                if ($status === 'low_stock') {
                    $query->where('stock', '>', 0)
                        ->whereColumn('stock', '<=', 'minimum_stock');
                }

                if ($status === 'safe') {
                    $query->whereColumn('stock', '>', 'minimum_stock');
                }
            })
            ->orderBy('name')
            ->paginate(20);

        $summary = [
            'total_products' => Product::count(),

            'total_stock' => Product::sum('stock'),

            'out_of_stock' => Product::where('stock', 0)->count(),

            'low_stock' => Product::where('stock', '>', 0)
                ->whereColumn('stock', '<=', 'minimum_stock')
                ->count(),

            'safe_stock' => Product::whereColumn(
                'stock',
                '>',
                'minimum_stock'
            )->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Laporan stok berhasil diambil.',
            'data' => [
                'summary' => $summary,
                'products' => $products,
            ],
        ]);
    }

    /**
     * Laporan seluruh transaksi stok.
     */
    public function transactions(Request $request)
    {
        $transactions = StockTransaction::query()
            ->with([
                'creator:id,name',
            ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'product_code_snapshot',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'product_name_snapshot',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'brand_name_snapshot',
                        'like',
                        "%{$search}%"
                    );
                });
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->whereHas('product', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            })
            ->when($request->brand_id, function ($query, $brandId) {
                $query->whereHas('product', function ($q) use ($brandId) {
                    $q->where('brand_id', $brandId);
                });
            })
            ->when($request->start_date, function ($query, $date) {
                $query->whereDate('transaction_date', '>=', $date);
            })
            ->when($request->end_date, function ($query, $date) {
                $query->whereDate('transaction_date', '<=', $date);
            })
            ->latest('transaction_date')
            ->paginate(20);

        $summaryQuery = StockTransaction::query()
            ->when($request->category_id, function ($query, $categoryId) {
                $query->whereHas('product', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            })
            ->when($request->brand_id, function ($query, $brandId) {
                $query->whereHas('product', function ($q) use ($brandId) {
                    $q->where('brand_id', $brandId);
                });
            })
            ->when($request->start_date, function ($query, $date) {
                $query->whereDate('transaction_date', '>=', $date);
            })
            ->when($request->end_date, function ($query, $date) {
                $query->whereDate('transaction_date', '<=', $date);
            });

        $totalIn = (clone $summaryQuery)
            ->where('type', 'IN')
            ->sum('quantity');

        $totalOut = (clone $summaryQuery)
            ->where('type', 'OUT')
            ->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Laporan transaksi berhasil diambil.',
            'data' => [
                'summary' => [
                    'total_in' => $totalIn,
                    'total_out' => $totalOut,
                ],
                'transactions' => $transactions,
            ],
        ]);
    }

    /**
     * Laporan keuntungan bulanan.
     */
    public function profit(Request $request)
    {
        $year = $request->integer('year', now()->year);

        $monthlyData = StockTransaction::query()
            ->selectRaw("
                MONTH(transaction_date) as month,
                SUM(
                    CASE
                        WHEN type = 'OUT'
                        THEN quantity * selling_price
                        ELSE 0
                    END
                ) as revenue,
                SUM(
                    CASE
                        WHEN type = 'OUT'
                        THEN quantity * purchase_price
                        ELSE 0
                    END
                ) as cost
            ")
            ->whereYear('transaction_date', $year)
            ->groupByRaw('MONTH(transaction_date)')
            ->orderByRaw('MONTH(transaction_date)')
            ->get();

        $months = collect(range(1, 12))->map(function ($month) use (
            $monthlyData
        ) {
            $data = $monthlyData->firstWhere('month', $month);

            $revenue = (float) ($data->revenue ?? 0);
            $cost = (float) ($data->cost ?? 0);

            return [
                'month' => $month,
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $revenue - $cost,
            ];
        });

        $totalRevenue = $months->sum('revenue');
        $totalCost = $months->sum('cost');
        $totalProfit = $months->sum('profit');

        return response()->json([
            'success' => true,
            'message' => 'Laporan keuntungan berhasil diambil.',
            'data' => [
                'year' => $year,
                'summary' => [
                    'revenue' => $totalRevenue,
                    'cost' => $totalCost,
                    'profit' => $totalProfit,
                ],
                'monthly' => $months,
            ],
        ]);
    }
}
