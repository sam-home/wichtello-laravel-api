<?php

namespace User\Models;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $confirm
 * @property bool $active
 * @property string $token
 * @property string $reset
 * @property bool $premium
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class User extends Model implements \Illuminate\Contracts\Auth\Authenticatable
{
    use SoftDeletes;
    use Authenticatable;

    protected $table = 'users';
    protected $hidden = ['password', 'token'];
    protected $casts = [
        'active' => 'bool',
        'premium' => 'bool'
    ];
}
