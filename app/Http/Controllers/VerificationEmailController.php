<?php

namespace App\Http\Controllers;

use App\Jobs\SendVerificationEmail;
use App\Services\User\VerificationService;
use Illuminate\Http\Request;

class VerificationEmailController extends Controller
{

    public function __construct(private VerificationService $service)
    {
        //
    }
    public function verify(Request $request , int $id, string $hash){
        $this->service->handle($request , $id , $hash);

        return redirect(config('app.frontend_url') . '/verify?status=success');
    }


    public function resendEmail(Request $request){
        $user = $request->user();
        dispatch(new SendVerificationEmail($user));
        return response()->json(['message' => 'Verification link sent']);
    }
}
