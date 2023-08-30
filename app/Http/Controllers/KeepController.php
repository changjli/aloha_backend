<?php

namespace App\Http\Controllers;

use App\Models\Keep;
use App\Http\Requests\StoreKeepRequest;
use App\Http\Requests\UpdateKeepRequest;

class KeepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreKeepRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Keep $keep)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Keep $keep)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKeepRequest $request, Keep $keep)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Keep $keep)
    {
        //
    }
}
