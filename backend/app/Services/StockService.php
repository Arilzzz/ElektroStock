<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function stockIn(
        int $productId,
        int $quantity,
        float $purchasePrice,
        string $transactionDate,
        ?string $description = null,
        ?float $sellingPrice = null
    ): StockTransaction {
        return DB::transaction(function () use (
            $productId,
            $quantity,
            $purchasePrice,
            $transactionDate,
            $description,
            $sellingPrice
        ) {
            $product = Product::lockForUpdate()
                ->with('brand')
                ->findOrFail($productId);

            $oldStock = $product->stock;
            $newStock = $oldStock + $quantity;

            // Update stock secara langsung,
            // bukan menggunakan mass assignment.
            $product->stock = $newStock;
            $product->purchase_price = $purchasePrice;
            if ($sellingPrice !== null && $sellingPrice > 0) {
                $product->selling_price = $sellingPrice;
            }
            $product->save();

            return StockTransaction::create([
                'product_id' => $product->id,
                'created_by' => Auth::id(),
                'type' => 'IN',

                'product_code_snapshot' => $product->code,
                'product_name_snapshot' => $product->name,
                'brand_name_snapshot' => $product->brand?->name,
                'product_type_snapshot' => $product->type_model,

                'purchase_price' => $purchasePrice,
                'selling_price' => $product->selling_price,

                'quantity' => $quantity,
                'description' => $description,
                'transaction_date' => $transactionDate,
            ]);
        });
    }

    public function stockOut(
        int $productId,
        int $quantity,
        float $sellingPrice,
        string $transactionDate,
        ?string $description = null
    ): StockTransaction {
        return DB::transaction(function () use (
            $productId,
            $quantity,
            $sellingPrice,
            $transactionDate,
            $description
        ) {
            $product = Product::lockForUpdate()
                ->with('brand')
                ->findOrFail($productId);

            // Cek stok
            if ($product->stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => [
                        'Stok tidak mencukupi. Stok tersedia: '
                        . $product->stock
                    ],
                ]);
            }

            $oldStock = $product->stock;
            $newStock = $oldStock - $quantity;

            // Harga modal yang digunakan untuk transaksi
            $purchasePrice = $product->purchase_price;

            // Update stock secara langsung
            $product->stock = $newStock;
            $product->selling_price = $sellingPrice;
            $product->save();

            return StockTransaction::create([
                'product_id' => $product->id,
                'created_by' => Auth::id(),
                'type' => 'OUT',

                'product_code_snapshot' => $product->code,
                'product_name_snapshot' => $product->name,
                'brand_name_snapshot' => $product->brand?->name,
                'product_type_snapshot' => $product->type_model,

                'purchase_price' => $purchasePrice,
                'selling_price' => $sellingPrice,

                'quantity' => $quantity,
                'description' => $description,
                'transaction_date' => $transactionDate,
            ]);
        });
    }
}
