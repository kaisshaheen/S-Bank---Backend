<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Pest\Support\Str;

class ResetPasswordController extends Controller
{
     public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $this->responseByStatus($status, Password::RESET_LINK_SENT);
    }

    public function reset(ResetRequest $request)
    {
        $request->validated();

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $this->responseByStatus($status, Password::PASSWORD_RESET);
    }


    private function responseByStatus(string $status, string $successStatus)
    {
        return $status === $successStatus
            ? response()->json(['message' => __($status)], 200)
            : response()->json(['message' => __($status)], 400);
    }
}
