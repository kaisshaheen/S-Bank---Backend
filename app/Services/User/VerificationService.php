<?php 

namespace App\Services\User;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class VerificationService{

    public function __construct(private UserRepository $userRepo)
    {
        //
    }

    public function handle(Request $request ,int $id , $hash){
        $user = $this->userRepo->findById($id);

        $user->checkHashing($hash);

        $user->checkSignature($request);
    
        if($user->hasVerifiedEmail()){
            return response()->json([
                'message' => 'Email already Verified'
            ]);
        }else{
            $user->markEmailAsVerified();
        }

    }

}