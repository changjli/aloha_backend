<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Save;
use App\Models\User;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class BlogApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $blogs = Blog::all();

            if ($request->category) {
                $blogs = Blog::whereHas('category', function ($q) use ($request) {
                    $q->where('name', $request->category);
                })->get();
            }

            foreach ($blogs as $blog) {
                $blog['user'] = $blog->user;
                $blog['category'] = $blog->category;
                $blog['comments'] = $blog->comment;
                $blog['likes'] = $blog->like;
                $blog['keeps'] = $blog->keep;
            }

            return ResponseController::successResponse($blogs);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    public function post(String $username)
    {
        try {
            $blogs = Blog::whereHas('user', function ($q) use ($username) {
                $q->where('username', $username);
            })->get();

            foreach ($blogs as $blog) {
                $blog['user'] = $blog->user;
                $blog['category'] = $blog->category;
                $blog['comments'] = $blog->comment;
                $blog['likes'] = $blog->like;
                $blog['keeps'] = $blog->keep;
            }

            return ResponseController::successResponse($blogs);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    public function liked(String $username)
    {
        try {
            $user = User::where('username', $username)->first();
            $user_id = $user->id;

            $blogs = Blog::whereHas('like', function ($q) use ($user_id) {
                $q->where('user_id', $user_id);
            })->get();

            foreach ($blogs as $blog) {
                $blog['user'] = $blog->user;
                $blog['category'] = $blog->category;
                $blog['comments'] = $blog->comment;
                $blog['likes'] = $blog->like;
                $blog['keeps'] = $blog->keep;
            }

            return ResponseController::successResponse($blogs);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    public function kept(String $username)
    {
        try {
            $user = User::where('username', $username)->first();
            $user_id = $user->id;

            $blogs = Blog::whereHas('keep', function ($q) use ($user_id) {
                $q->where('user_id', $user_id);
            })->get();

            foreach ($blogs as $blog) {
                $blog['user'] = $blog->user;
                $blog['category'] = $blog->category;
                $blog['comments'] = $blog->comment;
                $blog['likes'] = $blog->like;
                $blog['keeps'] = $blog->keep;
            }

            return ResponseController::successResponse($blogs);
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
        $user = auth()->user();
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'content' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseController::errorResponse($validator->errors(), 404);
            }

            $blog = Blog::create([
                'id' => IdGenerator::generate([
                    'table' => 'blogs',
                    'length' => '6',
                    'prefix' => 'BL',
                ]),
                'user_id' => $user->id,
                'category_id' => $request->category,
                'title' => $request->title,
                'description' => $request->description,
                'content' => $request->content,
            ]);

            SlugService::createSlug(Blog::class, 'slug', $blog->title);

            return ResponseController::successResponse($blog);
        } catch (Exception $e) {
            return ResponseController::errorResponse(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        try {
            // blog user

            // using with
            $blog = Blog::with('user')->where('id', $blog->id)->first();

            // using eloquent 
            // $blog['user'] = $blog->user;

            // using manual 

            // blog comment
            $blog['comment'] = Comment::with('user')->where('blog_id', $blog->id)->get();

            $blog['likes'] = Like::where('blog_id', $blog->id)
                ->select(DB::raw('count(*) as total'))
                ->groupBy(DB::raw('blog_id'))
                ->pluck('total');

            return ResponseController::successResponse($blog);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'content' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseController::errorResponse($validator->errors(), 404);
            }

            if ($request->title != $blog->title) {
                $blog->slug = null;
                SlugService::createSlug(Blog::class, 'slug', $request->title);
            }

            $blog->update([
                'title' => $request->title,
                'description' => $request->description,
                'content' => $request->content,
                'category_id' => $request->category,
            ]);

            $new = Blog::where('id', $blog->id)->first();

            return ResponseController::successResponse($new);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        try {
            $blog->delete();

            return ResponseController::successResponse([]);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    public function search(Request $request)
    {
        try {
            $blogs = Blog::where('title', 'like', '%' . $request->q . '%')
                ->orWhere('description', 'like', '%' . $request->q . '%')
                ->orWhere('content', 'like', '%' . $request->q . '%')
                ->get();

            foreach ($blogs as $blog) {
                $blog['user'] = $blog->user;
                $blog['category'] = $blog->category;
                $blog['comments'] = $blog->comment;
                $blog['likes'] = $blog->like;
                $blog['keeps'] = $blog->keep;
            }

            return ResponseController::successResponse($blogs);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }
}
