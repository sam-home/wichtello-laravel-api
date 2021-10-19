<?php

namespace Group\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $state // invite, start
 * @property string $name
 * @property string $join_link
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Group extends Model
{
    use SoftDeletes;

    protected $table = 'groups';

    protected $casts = [
        'user_id' => 'int'
    ];
}
