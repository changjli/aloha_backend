<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordEmail;
use App\Mail\ResetPasswordMail;
use App\Mail\UpdateEmail;
use App\Mail\VerificationMail;
use App\Models\User;
use App\Models\VerificationCode;
use Carbon\Carbon;
use Exception;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::all();
            return ResponseController::successResponse($users);
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
        try {
            // biar ga sembarang orang langsung bisa create account 
            $code = VerificationCode::where('code', $request->code)->first();

            if (!$code) {
                ResponseController::errorResponse('page doesnt exist', 404);
            }

            $validator = Validator::make($request->all(), [
                'email' => 'required|email:dns|unique:users,email',
                'username' => 'required|min:6|unique:users,username',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseController::errorResponse($validator->errors(), 400);
            }

            // create user 
            $user = User::create([
                'id' => IdGenerator::generate([
                    'table' => 'users',
                    'length' => '6',
                    'prefix' => 'US',
                ]),
                'email' => $request->email,
                'username' => $request->username,
                'password' => $request->password,
                'email_verified_at' => Carbon::now(),
            ]);

            $code->delete();

            // otomatis login
            $token = $user->createToken('auth_token')->plainTextToken;

            $user['token'] = $token;

            return ResponseController::successResponse($user);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        try {
            return ResponseController::successResponse($user);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $user = auth()->user();
        try {
            $path = $user->profile_picture;

            $rules = [
                'profile_name' => 'required',
            ];

            if ($request->username && $request->username != $user->username) {
                $rules['username'] = 'required|min:6|unique:users, username';
            }

            if ($request->image) {
                // kalo ada image baru validasi 
                $rules['image'] = 'required|image|max:2048';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return ResponseController::errorResponse($validator->errors(), 404);
            }

            if ($request->image) {
                // kalo ada old image delete dulu 
                if ($path) {
                    Storage::delete($path);
                }

                // store file 
                $image = $request->file('image');

                // store file path 
                $path = $image->store('profile', 'public');
            }

            User::where('id', $user->id)->update([
                'username' => $request->username,
                'profile_name' => $request->profile_name,
                'profile_picture' => $path
            ]);

            return ResponseController::successResponse([]);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    public function updateEmail(Request $request, User $user)
    {
        $user = auth()->user();
        try {
            if ($request->email == $user->email) {
                return ResponseController::successResponse([]);
            }

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users,email',
            ]);

            if ($validator->fails()) {
                return ResponseController::errorResponse($validator->errors(), 405);
            }

            $code = VerificationCode::where('email', $request->email);
            if ($code) {
                $code->delete();
            }

            $verificationCode = strval(rand(100000, 999999));
            VerificationCode::create([
                'email' => $request->email,
                'code' => $verificationCode,
            ]);

            if (Mail::to($request->email)->send(new UpdateEmail($verificationCode))) {
                return ResponseController::successResponse([]);
            }

            return ResponseController::errorResponse('Fail to send email', 400);

            return ResponseController::successResponse([]);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    public function verifyUpdateEmail(Request $request)
    {
        // try {
        //     $code = VerificationCode::where('code', $request->code)->first();

        //     if ($code) {
        //         User::where('email', $code->email)->update([
        //             'email' => $request->
        //         ])
        //         $code->delete();
        //         return ResponseController::successResponse([]);
        //     }

        //     return ResponseController::errorResponse('page doesnt exist', 404);
        // } catch (Exception $e) {
        //     return ResponseController::errorResponse($e->getMessage(), 400);
        // }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    public function register(Request $request)
    {
        try {
            // validasi 
            $validator = Validator::make($request->all(), [
                'email' => 'required|email:dns|unique:users',
            ]);

            if ($validator->fails()) {
                return ResponseController::errorResponse($validator->errors(), 400);
            }

            // resend code 
            $code = VerificationCode::where('email', $request->email);
            if ($code) {
                $code->delete();
            }

            // verifikasi buat sendiri
            $verificationCode = strval(rand(100000, 999999));
            VerificationCode::create([
                'email' => $request->email,
                'code' => $verificationCode,
            ]);

            if (Mail::to($request->email)->send(new VerificationMail($verificationCode))) {
                return ResponseController::successResponse([]);
            }

            return ResponseController::errorResponse('Fail to send email', 400);

            // verifikasi dari package gabisa
            // UserVerification::generate($user);
            // UserVerification::send($user, 'Verification Email');
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 400);
        }
    }

    // verifikasi kalo pake link
    public function verificationRegister(Request $request)
    {
        try {
            $code = VerificationCode::where('code', $request->code)->first();

            if ($code) {
                return ResponseController::successResponse($code);
            }

            return ResponseController::errorResponse('page doesnt exist', 404);

            // $user = User::whereHas('VerificationCode', function ($q) use ($code) {
            //     $q->where('code', 'like', $code);
            // })->first();

            // if ($user) {
            //     $user->update([
            //         'email_verified_at' => Carbon::now()
            //     ]);
            //     $user->VerificationCode->delete();
            // }
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 400);
        }
    }

    // verifikasi kalo pake code
    public function verification2(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseController::errorResponse($validator->errors(), 400);
            }

            $user = User::where('email', $request->email)->first();

            if ($user->VerificationCode->code == $request->code) {
                $user->update([
                    'email_verified_at' => Carbon::now(),
                ]);
                $user->VerificationCode->delete();
            }
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 400);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseController::errorResponse($validator->errors(), 400);
            }

            if (!Auth::attempt($request->only('email', 'password'))) {
                return ResponseController::errorResponse(['error' => 'email or password error'], 400);
            }

            $user = User::where('email', $request->email)->first();

            $token = $user->createToken('auth_token')->plainTextToken;

            $user['token'] = $token;

            return ResponseController::successResponse($user);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    // forgot password
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return ResponseController::errorResponse($validator->errors(), 400);
            }

            $user = User::where('email', $request->email)->first();

            if ($user == null) {
                return ResponseController::errorResponse('user doesnt exist', 405);
            }

            $token =  DB::table('password_resets')->where('email', $request->email)->first();
            if ($token) {
                DB::table('password_resets')->where('email', $request->email)->delete();
            }

            $token = strval(rand(100000, 999999));
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);

            if (Mail::to($request->email)->send(new ForgotPasswordEmail($token))) {
                return ResponseController::successResponse([]);
            }

            return ResponseController::errorResponse('Fail to send email', 400);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    public function verifyForgotPassword(Request $request)
    {
        try {
            $token =  DB::table('password_resets')->where('token', $request->token)->first();

            if ($token) {
                return ResponseController::successResponse($token);
            }

            return ResponseController::errorResponse('page doesnt exist', 404);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 400);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseController::errorResponse($validator->errors(), 400);
            }

            $token =  DB::table('password_resets')->where('token', $request->token)->first();

            if (!$token) {
                return ResponseController::errorResponse([], 404);
            }

            $user = User::where('email', $token->email)->first();

            if ($user) {
                $user->update([
                    'password' => $request->password,
                ]);

                DB::table('password_resets')->where('token', $request->token)->delete();

                return ResponseController::successResponse([]);
            }

            return ResponseController::errorResponse('error', 404);
        } catch (Exception $e) {
            return ResponseController::errorResponse($e->getMessage(), 404);
        }
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return ResponseController::successResponse([]);
    }
}
