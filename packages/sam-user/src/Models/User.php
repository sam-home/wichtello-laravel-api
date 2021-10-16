<?php

namespace Sam\User\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $confirm
 * @property bool $active
 * @property string $token
 * @property string $reset
 */
class User extends Model implements \Illuminate\Contracts\Auth\Authenticatable
{
    use Authenticatable;

    protected $table = 'users';
    protected $hidden = ['password', 'token'];
    protected $casts = [
        'active' => 'bool'
    ];
}
