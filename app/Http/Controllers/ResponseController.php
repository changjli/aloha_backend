<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public static function errorResponse($message, $code)
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }

    public static function successResponse($data)
    {
        return response()->json(['success' => true, 'data' => $data]);
    }
}
