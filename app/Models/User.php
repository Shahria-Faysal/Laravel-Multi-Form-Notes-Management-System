<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'password' => 'hashed'
        ];
    }

    protected $hidden = [
       'password',
       'remember_token',
    ];
}
