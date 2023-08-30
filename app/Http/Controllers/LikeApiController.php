<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Exception;
use Illuminate\Http\Request;

class LikeApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function like(Request $request)
    {
        $user = auth()->user();
        try {
            $like = Like::where('user_id', $user->id)->where('blog_id', $request->blog_id)->first();

            if ($like) {
                Like::where('user_id', $user->id)->where('blog_id', $request->blog_id)->delete();
                return ResponseController::successResponse([]);
            }

            $like = Like::create([
                'user_id' => $user->id,
                'blog_id' => $request->blog_id,
            ]);

            return ResponseController::successResponse($like);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    public function isLike(String $slug)
    {
        $user = auth()->user();
        try {
            $like = Like::where('user_id', $user->id)->whereHas('blog', function ($q) use ($slug) {
                $q->where('slug', $slug);
            })->first();

            return ResponseController::successResponse($like);
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
    public function show(Like $like)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Like $like)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Like $like)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Like $like)
    {
        //
    }
}
