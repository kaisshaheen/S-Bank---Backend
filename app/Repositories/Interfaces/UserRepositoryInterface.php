<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface{

    public function fetchUsers($search , $role , $status) :LengthAwarePaginator;
    public function create(array $data): User;
    public function findByEmail($email) ;
    public function findById(int $id): User;

    public function customerCount(): int;
}