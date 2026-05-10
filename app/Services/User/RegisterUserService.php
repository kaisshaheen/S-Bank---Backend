<?php

namespace App\Services\User;

use App\Repositories\UserRepository;

class RegisterUserService{

    public function __construct(private UserRepository $users)
    {
        //
    }

    public function handle($data){
        $user = $this->users->create($data);
        return $user;
    }

}