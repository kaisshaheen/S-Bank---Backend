<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
{

    public function __construct(private UserRepositoryInterface $user)
    {
       //
    }
    public function index(Request $request)
    {
        $users = $this->user->fetchUsers(
            $request->input('search', ''),
            $request->input('role', ''),
            $request->input('status', '')
        );

        return response()->json($users);
    }

    public function show(User $user)
    {
        return response()->json([
            'user' => $user->load('account.loans', 'account.transactions')
        ]);
    }

    public function ban(User $user)
    {
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot ban an admin'], 403);
        }

        $user->update(['status' => $user->status === 'banned' ? 'active' : 'banned']);

        return response()->json(['message' => 'User status updated', 'user' => $user]);
    }
}
