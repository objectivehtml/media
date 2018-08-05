<?php

namespace Tests;

use Objectivehtml\Media\Mediable;
use Illuminate\Foundation\Auth\User as Model;

class User extends Model
{
    use Mediable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password'
    ];
    
}
