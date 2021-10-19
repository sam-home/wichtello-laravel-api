<?php

namespace Message\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Message extends Model
{
    use SoftDeletes;

    protected $table = 'wishes';
    protected $casts = [
        'group_id' => 'int',
        'user_id' => 'int'
    ];
}
