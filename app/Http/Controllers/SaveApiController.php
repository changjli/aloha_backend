<?php

namespace App\Http\Controllers;

use App\Models\Save;
use Exception;
use Illuminate\Http\Request;

class SaveApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function save(Request $request)
    {
        $user = auth()->user();
        try {
            $save = Save::where('user_id', $user->id)->where('blog_id', $request->blog_id)->first();

            if ($save) {
                Save::where('user_id', $user->id)->where('blog_id', $request->blog_id)->delete();
                return ResponseController::successResponse([]);
            }

            $save = Save::create([
                'user_id' => $user->id,
                'blog_id' => $request->blog_id,
            ]);

            return ResponseController::successResponse($save);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
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
    public function show(Save $save)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Save $save)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Save $save)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Save $save)
    {
        //
    }
}
