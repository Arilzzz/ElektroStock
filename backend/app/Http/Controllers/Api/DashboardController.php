<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\StockTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Statistik Produk
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::count();

        $totalStock = Product::sum('stock');

        $lowStock = Product::where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->count();

        $outOfStock = Product::where('stock', 0)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Statistik Master Data
        |--------------------------------------------------------------------------
        */

        $totalCategories = Category::count();

        $totalBrands = Brand::count();

        /*
        |--------------------------------------------------------------------------
        | Produk Stok Menipis
        |--------------------------------------------------------------------------
        */

        $lowStockProducts = Product::with([
            'category:id,name',
            'brand:id,name',
        ])
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->limit(10)
            ->get([
                'id',
                'category_id',
                'brand_id',
                'code',
                'name',
                'type_model',
                'stock',
                'minimum_stock',
                'unit',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Produk Stok Habis
        |--------------------------------------------------------------------------
        */

        $outOfStockProducts = Product::with([
            'category:id,name',
            'brand:id,name',
        ])
            ->where('stock', 0)
            ->latest()
            ->limit(10)
            ->get([
                'id',
                'category_id',
                'brand_id',
                'code',
                'name',
                'type_model',
                'stock',
                'minimum_stock',
                'unit',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Aktivitas Transaksi Terbaru
        |--------------------------------------------------------------------------
        */

        $recentTransactions = StockTransaction::query()
            ->with([
                'creator:id,name',
            ])
            ->latest('transaction_date')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Statistik Keuangan Bulan Ini
        |--------------------------------------------------------------------------
        */

        $currentMonthTransactions = StockTransaction::query()
            ->where('type', 'OUT')
            ->whereYear(
                'transaction_date',
                now()->year
            )
            ->whereMonth(
                'transaction_date',
                now()->month
            )
            ->get([
                'quantity',
                'purchase_price',
                'selling_price',
            ]);

        $revenue = $currentMonthTransactions->sum(function ($transaction) {
            return $transaction->quantity
                * $transaction->selling_price;
        });

        $cost = $currentMonthTransactions->sum(function ($transaction) {
            return $transaction->quantity
                * $transaction->purchase_price;
        });

        $profit = $revenue - $cost;

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' => 'Data dashboard berhasil diambil.',

            'data' => [

                'statistics' => [
                    'total_products' => $totalProducts,
                    'total_stock' => $totalStock,
                    'low_stock' => $lowStock,
                    'out_of_stock' => $outOfStock,
                    'total_categories' => $totalCategories,
                    'total_brands' => $totalBrands,
                ],

                'financial' => [
                    'month' => now()->format('Y-m'),
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $profit,
                ],

                'low_stock_products' => $lowStockProducts,

                'out_of_stock_products' => $outOfStockProducts,

                'recent_transactions' => $recentTransactions,
            ],
        ]);
    }
}
