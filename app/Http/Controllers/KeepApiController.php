<?php

namespace App\Http\Controllers;

use App\Models\Keep;
use Exception;
use Illuminate\Http\Request;

class KeepApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function keep(Request $request)
    {
        $user = auth()->user();
        try {
            $keep = Keep::where('user_id', $user->id)->where('blog_id', $request->blog_id)->first();

            if ($keep) {
                Keep::where('user_id', $user->id)->where('blog_id', $request->blog_id)->delete();
                return ResponseController::successResponse([]);
            }

            $keep = Keep::create([
                'user_id' => $user->id,
                'blog_id' => $request->blog_id,
            ]);

            return ResponseController::successResponse($keep);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    public function isKeep(String $slug)
    {
        $user = auth()->user();
        try {
            $keep = Keep::where('user_id', $user->id)->whereHas('blog', function ($q) use ($slug) {
                $q->where('slug', $slug);
            })->first();

            return ResponseController::successResponse($keep);
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
    public function update(Request $request, Keep $keep)
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
