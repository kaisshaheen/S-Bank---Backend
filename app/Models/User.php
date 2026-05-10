<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;
use Symfony\Component\HttpFoundation\Request;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable , HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function isAdmin() 
    {
        return $this->role === 'admin';
    }

    public function account()
    {
        return $this->hasOne(Account::class);
    }


    public function checkHashing(string $hash){

        if (! hash_equals((string) $hash, sha1($this->getEmailForVerification()))) {
            abort(403, 'Invalid verification link');
        }

    }

    public function checkSignature(Request $request){

        if (! URL::hasValidSignature($request)) {
            return response()->json(['message' => 'Invalid or expired link'], 403);
        }

    }

    public function scopeSearch($query , ?string $search){
        return $query->when($search, fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
        );
    }

    public function scopeOfRole($query , ?string $role){
        return $query->when($role, fn($q) =>
                $q->where('role', $role)
        );
    }

    public function scopeOfstatus($query , ?string $status){
        return $query->when($status , fn($q)=>
            $q->where('status', $status)
        );
    }

}
