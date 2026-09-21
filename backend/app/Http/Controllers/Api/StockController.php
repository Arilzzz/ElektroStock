<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockInRequest;
use App\Http\Requests\StoreStockOutRequest;
use App\Models\StockTransaction;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {
    }

    /**
     * Stock In
     */
    public function stockIn(StoreStockInRequest $request)
    {
        $transaction = $this->stockService->stockIn(
            productId: $request->integer('product_id'),
            quantity: $request->integer('quantity'),
            purchasePrice: (float) $request->input('purchase_price'),
            transactionDate: $request->input('transaction_date'),
            description: $request->input('description'),
            sellingPrice: $request->filled('selling_price') ? (float) $request->input('selling_price') : null,
        );

        $transaction->load([
            'product.category',
            'product.brand',
            'creator',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stok berhasil ditambahkan.',
            'data' => $transaction,
        ], 201);
    }

    /**
     * Stock Out
     */
    public function stockOut(StoreStockOutRequest $request)
    {
        $transaction = $this->stockService->stockOut(
            productId: $request->product_id,
            quantity: $request->quantity,
            sellingPrice: $request->selling_price,
            transactionDate: $request->transaction_date,
            description: $request->description
        );

        $transaction->load([
            'product.category:id,name',
            'product.brand:id,name',
            'creator:id,name',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock berhasil dikeluarkan.',
            'data' => $transaction,
        ], 201);
    }

    /**
     * History transaksi
     */
    public function history(Request $request)
    {
        $transactions = StockTransaction::query()
            ->with([
                'product:id,category_id,brand_id,code,name,type_model',
                'creator:id,name',
            ])

            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where(
                        'product_name_snapshot',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'product_code_snapshot',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'brand_name_snapshot',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'product_type_snapshot',
                        'like',
                        "%{$search}%"
                    );
                });
            })

            ->when($request->type, function ($query, $type) {
                $query->where('type', strtoupper($type));
            })

            ->when($request->product_id, function ($query, $productId) {
                $query->where('product_id', $productId);
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
                $query->whereDate(
                    'transaction_date',
                    '>=',
                    $date
                );
            })

            ->when($request->end_date, function ($query, $date) {
                $query->whereDate(
                    'transaction_date',
                    '<=',
                    $date
                );
            })

            ->latest('transaction_date')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Histori stock berhasil diambil.',
            'data' => $transactions,
        ]);
    }

    /**
     * Detail transaksi
     */
    public function show(StockTransaction $stockTransaction)
    {
        $stockTransaction->load([
            'product.category:id,name',
            'product.brand:id,name',
            'creator:id,name',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi berhasil diambil.',
            'data' => $stockTransaction,
        ]);
    }
}
