<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Brand2;
use Illuminate\Http\Request;

class Brand2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Brand::all(), 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand2 $brand2)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand2 $brand2)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand2 $brand2)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand2 $brand2)
    {
        //
    }
}
