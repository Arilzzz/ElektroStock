<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Menampilkan daftar produk.
     */
    public function index(Request $request)
    {
        $products = Product::with([
            'category:id,name',
            'brand:id,name',
        ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
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
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Data produk berhasil diambil.',
            'data' => $products,
        ]);
    }

    /**
     * Menyimpan produk baru.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request
                ->file('image')
                ->store('products', 'public');
        }

        // Produk baru selalu dimulai dengan stok 0.
        $data['stock'] = 0;

        $product = Product::create($data);

        $product->load([
            'category:id,name',
            'brand:id,name',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan.',
            'data' => $product,
        ], 201);
    }

    /**
     * Menampilkan detail produk.
     */
    public function show(Product $product)
    {
        $product->load([
            'category:id,name',
            'brand:id,name',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil.',
            'data' => $product,
        ]);
    }

    /**
     * Mengubah data produk.
     */
    public function update(
        UpdateProductRequest $request,
        Product $product
    ) {
        $data = $request->validated();

        if ($request->hasFile('image')) {

            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $request
                ->file('image')
                ->store('products', 'public');
        }

        $product->update($data);

        $product->load([
            'category:id,name',
            'brand:id,name',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
            'data' => $product,
        ]);
    }

    /**
     * Menghapus produk.
     */
    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus. Histori transaksi tetap dipertahankan.',
        ]);
    }
}
