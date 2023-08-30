<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Exception;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentApiController extends Controller
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
    public function store(Request $request)
    {
        $user = auth()->user();
        try {
            $validator = Validator::make($request->all(), [
                'content' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseController::errorResponse($validator->errors(), 404);
            }

            $comment = Comment::create([
                'id' => IdGenerator::generate([
                    'table' => 'comments',
                    'length' => '6',
                    'prefix' => 'CO',
                ]),
                'content' => $request->content,
                'user_id' => $user->id,
                'blog_id' => $request->blog_id,
                'parent_id' => $request->parent_id,
            ]);

            return ResponseController::successResponse($comment);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        try {
            $validator = Validator::make($request->all(), [
                'content' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseController::errorResponse($validator->errors(), 404);
            }

            $comment->update([
                'content' => $request->content,
            ]);

            $comment = Comment::where('id', $comment->id);

            return ResponseController::successResponse($comment);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        try {
            $comment->delete();

            return ResponseController::successResponse([]);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }
}
