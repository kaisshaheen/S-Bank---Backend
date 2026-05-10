<?php 

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use stdClass;

class UserRepository implements UserRepositoryInterface{


    public function fetchUsers($search ,  $role  ,  $status): LengthAwarePaginator{
        return User::with('account')
            ->search($search)
            ->ofRole($role)
            ->ofstatus($status)
            ->latest()
            ->paginate(10);
    }
    public function create(array $data): User{
        return User::create($data);
    }

    public function findByEmail($email){
        return User::where("email" , $email)->first();
    }

    public function findById(int $id): User{
        return User::findOrFail($id);
    } 

    public function customerCount(): int
    {
        return User::where('role', 'customer')->count();
    }
}