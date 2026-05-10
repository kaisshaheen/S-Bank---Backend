<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\Account\CreateAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{


    public function __construct(private CreateAccountService $createAccount)
    {
        //
    }
    

    public function create(Request $request){

        $request->validate([
            'type' => 'required|in:saving,current',
            'national_number' => 'required|string|max:11|min:11|unique:accounts,national_number',
            'password' => 'required|string|min:8|confirmed'
        ]);

        try {

            $account = $this->createAccount->handle([
                'user_id' => Auth::id(),
                'type' => $request->type,
                'status' => 'active',
                'national_number' => $request->national_number,
                'password' => Hash::make($request->password),
                'balance' => 0
            ]);

            return response()->json([
                'message' => 'Account created successfully',
                'account' => $account
            ], 201);

        } catch (\Throwable $e) {

            return response()->json([
                'message' => 'Failed to create account',
                'error' => $e->getMessage()
            ], 500);
        }
    }
      
    
    public function loginToAccount(Request $request){

        $request->validate([
            'password' => 'required|string|min:8'
        ]);


        if (!Auth::user()->account) {
            return response()->json(['message' => 'No account found for this user'], 404);
        }

        if(Auth::user()->account->status !== 'active'){
            return response()->json(['message' => 'Account is not active'], 403);
        }   

        if (!Hash::check($request->password, Auth::user()->account->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        

        return response()->json([
            'message' => 'Login successful',
            'account' => [
                'account_number' => Auth::user()->account->account_number,
                'balance' => Auth::user()->account->balance,
            ]
        ], 200);
    }

    public function myAccount(){

        $account = Auth::user()->account;


        $data = Cache::remember(
            "account:{$account->id}:data",
            300,
       function () use ($account) {
                return [
                    'account_owner'  => $account->user->name,
                    'account_number' => $account->account_number,
                    'balance'        => $account->balance,
                ];
            }
        );


        return response()->json([
            'account' => $data
        ], 200);

    }
}