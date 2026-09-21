<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;

class BrandController extends Controller
{
    /**
     * Menampilkan semua brand.
     */
    public function index()
    {
        $brands = Brand::withCount('products')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data brand berhasil diambil.',
            'data' => $brands,
        ]);
    }

    /**
     * Menyimpan brand baru.
     */
    public function store(StoreBrandRequest $request)
    {
        $brand = Brand::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Brand berhasil ditambahkan.',
            'data' => $brand,
        ], 201);
    }

    /**
     * Menampilkan satu brand.
     */
    public function show(Brand $brand)
    {
        $brand->loadCount('products');

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data brand berhasil diambil.',
            'data' => $brand,
        ]);
    }

    /**
     * Mengubah brand.
     */
    public function update(
        UpdateBrandRequest $request,
        Brand $brand
    ) {
        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand tidak ditemukan.',
            ], 404);
        }

        $brand->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Brand berhasil diperbarui.',
            'data' => $brand->fresh(),
        ]);
    }

    /**
     * Menghapus brand.
     */
    public function destroy(Brand $brand)
    {
        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand tidak ditemukan.',
            ], 404);
        }

        if ($brand->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Brand tidak dapat dihapus karena masih digunakan oleh produk.',
            ], 422);
        }

        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brand berhasil dihapus.',
        ]);
    }
}
