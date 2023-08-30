<?php

use App\Http\Controllers\BlogApiController;
use App\Http\Controllers\CategoryApiController;
use App\Http\Controllers\CommentApiController;
use App\Http\Controllers\KeepApiController;
use App\Http\Controllers\LikeApiController;
use App\Http\Controllers\MailApiController;
use App\Http\Controllers\SaveApiController;
use App\Http\Controllers\UserApiController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// register
// request email
Route::post('/register', [UserApiController::class, 'register']);

// verify link
Route::get('/register/verify', [UserApiController::class, 'verificationRegister']);

// create account
Route::post('/user', [UserApiController::class, 'store']);

//login
Route::post('/login', [UserApiController::class, 'login']);

// forgot password 
Route::post('/forgot-password', [UserApiController::class, 'forgotPassword']);

Route::get('/forgot-password/verify', [UserApiController::class, 'verifyForgotPassword']);

Route::post('/reset-password', [UserApiController::class, 'resetPassword']);

// guest
Route::get('/blog', [BlogApiController::class, 'index']);

Route::get('/blog/{blog:slug}', [BlogApiController::class, 'show']);

Route::get('/blog/post/{username}', [BlogApiController::class, 'post']);

Route::get('/blog/like/{username}', [BlogApiController::class, 'liked']);

Route::get('/blog/keep/{username}', [BlogApiController::class, 'kept']);

Route::get('/search', [BlogApiController::class, 'search']);

Route::get('/category', [CategoryApiController::class, 'index']);

Route::get('/category/{category:name}', [CategoryApiController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    // account 
    Route::get('/user/{user:username}', [UserApiController::class, 'show']);

    // ga pake slug karena otomatis refer ke token
    Route::post('/user/update', [UserApiController::class, 'update']);

    // update email kaya verify register
    Route::put('/user/email', [UserApiController::class, 'updateEmail']);

    Route::get('/user/email/verify', [UserApiController::class, 'verifyUpdateEmail']);

    Route::delete('/user', [UserApiController::class, 'destroy']);

    // blog 
    Route::post('/blog', [BlogApiController::class, 'store']);

    Route::put('/blog/{blog:slug}', [BlogApiController::class, 'update']);

    Route::delete('/blog/{blog:slug}', [BlogApiController::class, 'destroy']);

    // comment
    Route::resource('/comment', CommentApiController::class);

    Route::post('/logout', [UserApiController::class, 'logout']);

    // like 
    Route::post('/like', [LikeApiController::class, 'like']);

    Route::get('/like/{slug}', [LikeApiController::class, 'isLike']);

    // keep
    Route::post('/keep', [KeepApiController::class, 'keep']);

    Route::get('/keep/{slug}', [KeepApiController::class, 'isKeep']);
});
