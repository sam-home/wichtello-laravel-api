<?php

namespace Group\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $group_id
 * @property int $creator_id
 * @property int $user_id
 * @property Carbon|null $joined_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class GroupUser extends Model
{
    use SoftDeletes;

    protected $table = 'group_users';

    protected $casts = [
        'group_id' => 'int',
        'creator_id' => 'int',
        'user_id' => 'int'
    ];
}
