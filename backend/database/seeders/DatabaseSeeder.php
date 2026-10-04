<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

        if (DB::getDriverName() === 'pgsql') {
            foreach (['users', 'brands', 'categories', 'products', 'stock_transactions'] as $table) {
                if (DB::table($table)->exists()) {
                    DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), coalesce(max(id), 1)) FROM {$table};");
                }
            }
        }

        // 1. Users
        $user1 = User::updateOrCreate(
            ['email' => 'admin@lilikelektronik.test'],
            [
                'id' => 1,
                'name' => 'Admin Lilik Elektronik',
                'password' => Hash::make('admin12345'),
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'id' => 2,
                'name' => 'Admin Lilik Elektronik',
                'password' => Hash::make('admin12345'),
            ]
        );

        // 2. Brands
        $brands = [
            ['name' => 'Polytron', 'description' => 'Brand elektronik Polytron'],
            ['name' => 'Coocaa', 'description' => 'Brand smart TV & elektronik Coocaa'],
            ['name' => 'LG', 'description' => 'Brand elektronik LG'],
            ['name' => 'Samsung', 'description' => 'Brand elektronik Samsung'],
            ['name' => 'Aqua', 'description' => 'Brand elektronik rumah tangga Aqua Japan'],
            ['name' => 'Maspion', 'description' => 'Brand peralatan rumah tangga Maspion'],
            ['name' => 'Miyako', 'description' => 'Brand peralatan dapur & rumah tangga Miyako'],
            ['name' => 'Philips', 'description' => 'Brand elektronik & pencahayaan Philips'],
            ['name' => 'Luby', 'description' => 'Brand lampu & alat listrik Luby'],
            ['name' => 'Panasonic', 'description' => 'Brand elektronik Panasonic'],
            ['name' => 'Sharp', 'description' => 'Brand elektronik Sharp'],
            ['name' => 'Cosmos', 'description' => 'Brand peralatan rumah tangga Cosmos'],
            ['name' => 'Sogo', 'description' => 'Brand peralatan elektronik Sogo'],
            ['name' => 'Toshiba', 'description' => 'Brand elektronik Toshiba'],
            ['name' => 'TD', 'description' => 'Brand peralatan elektronik & kipas TD'],
            ['name' => 'Rinnai', 'description' => 'Brand kompor gas & peralatan dapur Rinnai'],
            ['name' => 'Rinrei', 'description' => 'Brand perlengkapan elektronik & audio Rinrei'],
            ['name' => 'WinnGas', 'description' => 'Brand regulator & perlengkapan gas Winn Gas'],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['name' => $brand['name']], $brand);
        }

        // 3. Categories
        $categories = [
            ['name' => 'Kulkas', 'description' => 'Kategori produk kulkas / lemari es'],
            ['name' => 'Mesin Cuci', 'description' => 'Kategori produk mesin cuci'],
            ['name' => 'Setrika', 'description' => 'Kategori produk setrika listrik'],
            ['name' => 'Televisi', 'description' => 'Kategori produk smart TV & televisi'],
            ['name' => 'Kipas Angin', 'description' => 'Kategori produk kipas angin & pendingin'],
            ['name' => 'Magic Com', 'description' => 'Kategori produk penanak nasi / magic com'],
            ['name' => 'Regulator Gas', 'description' => 'Kategori produk regulator gas & perlengkapan kompor'],
            ['name' => 'Blender', 'description' => 'Kategori produk blender & food processor'],
            ['name' => 'Lampu', 'description' => 'Kategori produk lampu & pencahayaan LED'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }

        // 4. Products
        $samsungBrand = Brand::where('name', 'Samsung')->first();
        $tvCategory = Category::where('name', 'Televisi')->first();

        $product1 = Product::updateOrCreate(
            ['code' => 'TV-SAM-001'],
            [
                'id' => 1,
                'category_id' => $tvCategory?->id ?? 2,
                'brand_id' => $samsungBrand?->id ?? 2,
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

        if (DB::getDriverName() === 'pgsql') {
            foreach (['users', 'brands', 'categories', 'products', 'stock_transactions'] as $table) {
                if (DB::table($table)->exists()) {
                    DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), coalesce(max(id), 1)) FROM {$table};");
                }
            }
        }

        User::reguard();
        Brand::reguard();
        Category::reguard();
        Product::reguard();
        StockTransaction::reguard();
    }
}
