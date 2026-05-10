<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Jobs\SendVerificationEmail;

use App\Services\User\RegisterUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{


    public function __construct(private RegisterUserService $registerUserService)
    {
        //
    }

    public function register(RegisterRequest $request){
        $field = $request->validated();

        $user = $this->registerUserService->handle($field);

        dispatch(new SendVerificationEmail($user));

        Auth::login($user);

        return response()->json([
            'user' => $user
        ] , 201);
    }

    public function login(LoginRequest $request){
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }   

        $request->session()->regenerate();

        $user = Auth::user();

        return response()->json([
            'user' => [
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ]
        ] , 200);
    }

    public function logout(Request $request){
       
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json([
            'message'=>'Logged out successfully'
        ] , 200);
    }
}