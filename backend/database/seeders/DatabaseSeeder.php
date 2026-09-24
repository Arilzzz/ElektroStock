<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::unguard();
        Brand::unguard();
        Category::unguard();
        Product::unguard();
        StockTransaction::unguard();

        // 1. Users
        $user1 = User::updateOrCreate(
            ['email' => 'admin@electrostock.test'],
            [
                'id' => 1,
                'name' => 'Admin ElectroStock',
                'password' => Hash::make('admin12345'),
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'id' => 2,
                'name' => 'Admin ElectroStock',
                'password' => Hash::make('admin12345'),
            ]
        );

        // 2. Brands
        $brands = [
            ['id' => 2, 'name' => 'Samsung', 'description' => 'Brand elektronik Samsung'],
            ['id' => 3, 'name' => 'LG', 'description' => 'Brand elektronik LG'],
            ['id' => 4, 'name' => 'Polytron', 'description' => 'Brand elektronik Polytron'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(['id' => $brand['id']], $brand);
        }

        // 3. Categories
        $categories = [
            ['id' => 2, 'name' => 'Televisi', 'description' => 'Kategori produk televisi'],
            ['id' => 3, 'name' => 'Kulkas', 'description' => 'Kategori produk Kulkas'],
            ['id' => 4, 'name' => 'Mesin Cuci', 'description' => 'Kategori produk mesin cuci'],
            ['id' => 5, 'name' => 'Kipas Angin', 'description' => 'Kategori produk kipas angin'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['id' => $category['id']], $category);
        }

        // 4. Products
        $product1 = Product::updateOrCreate(
            ['code' => 'TV-SAM-001'],
            [
                'id' => 1,
                'category_id' => 2,
                'brand_id' => 2,
                'name' => 'Samsung Smart TV 43',
                'type_model' => 'UA43T6500',
                'description' => 'Smart TV Samsung 43 inch',
                'image' => null,
                'purchase_price' => 4100000.00,
                'selling_price' => 5100000.00,
                'stock' => 30,
                'minimum_stock' => 3,
                'unit' => 'unit',
            ]
        );

        // 5. Stock Transactions
        $transactions = [
            [
                'id' => 1,
                'product_id' => 1,
                'created_by' => 1,
                'type' => 'IN',
                'product_code_snapshot' => 'TV-SAM-001',
                'product_name_snapshot' => 'Samsung Smart TV 43',
                'brand_name_snapshot' => 'Samsung',
                'product_type_snapshot' => 'UA43T6500',
                'purchase_price' => 4000000.00,
                'selling_price' => 4500000.00,
                'quantity' => 10,
                'description' => 'Pembelian stok dari supplier',
                'transaction_date' => '2026-09-19 19:00:00',
            ],
            [
                'id' => 2,
                'product_id' => 1,
                'created_by' => 1,
                'type' => 'IN',
                'product_code_snapshot' => 'TV-SAM-001',
                'product_name_snapshot' => 'Samsung Smart TV 43',
                'brand_name_snapshot' => 'Samsung',
                'product_type_snapshot' => 'UA43T6500',
                'purchase_price' => 4000000.00,
                'selling_price' => 4500000.00,
                'quantity' => 10,
                'description' => 'Pembelian stok dari supplier',
                'transaction_date' => '2026-09-19 19:00:00',
            ],
            [
                'id' => 3,
                'product_id' => 1,
                'created_by' => 2,
                'type' => 'IN',
                'product_code_snapshot' => 'TV-SAM-001',
                'product_name_snapshot' => 'Samsung Smart TV 43',
                'brand_name_snapshot' => 'Samsung',
                'product_type_snapshot' => 'UA43T6500',
                'purchase_price' => 4000000.00,
                'selling_price' => 4500000.00,
                'quantity' => 10,
                'description' => 'Stok masuk dari supplier',
                'transaction_date' => '2026-09-21 09:00:00',
            ],
            [
                'id' => 4,
                'product_id' => 1,
                'created_by' => 2,
                'type' => 'OUT',
                'product_code_snapshot' => 'TV-SAM-001',
                'product_name_snapshot' => 'Samsung Smart TV 43',
                'brand_name_snapshot' => 'Samsung',
                'product_type_snapshot' => 'UA43T6500',
                'purchase_price' => 4000000.00,
                'selling_price' => 5000000.00,
                'quantity' => 2,
                'description' => 'Penjualan kepada pelanggan',
                'transaction_date' => '2026-09-21 10:00:00',
            ],
            [
                'id' => 5,
                'product_id' => 1,
                'created_by' => 2,
                'type' => 'IN',
                'product_code_snapshot' => 'TV-SAM-001',
                'product_name_snapshot' => 'Samsung Smart TV 43',
                'brand_name_snapshot' => 'Samsung',
                'product_type_snapshot' => 'UA43T6500',
                'purchase_price' => 4100000.00,
                'selling_price' => 5000000.00,
                'quantity' => 5,
                'description' => 'Tambahan stok supplier',
                'transaction_date' => '2026-09-21 11:00:00',
            ],
            [
                'id' => 6,
                'product_id' => 1,
                'created_by' => 2,
                'type' => 'OUT',
                'product_code_snapshot' => 'TV-SAM-001',
                'product_name_snapshot' => 'Samsung Smart TV 43',
                'brand_name_snapshot' => 'Samsung',
                'product_type_snapshot' => 'UA43T6500',
                'purchase_price' => 4100000.00,
                'selling_price' => 5100000.00,
                'quantity' => 3,
                'description' => 'Penjualan pelanggan',
                'transaction_date' => '2026-09-21 12:00:00',
            ],
        ];

        foreach ($transactions as $tx) {
            StockTransaction::updateOrCreate(['id' => $tx['id']], $tx);
        }

        User::reguard();
        Brand::reguard();
        Category::reguard();
        Product::reguard();
        StockTransaction::reguard();
    }
}
