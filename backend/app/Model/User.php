<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HasFactory;
    
    protected $table = 'users';
    protected $fillable = ['first_name', 'last_name', 'email', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
}