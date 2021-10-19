<?php

namespace Message\Models;

use Carbon\Carbon;
use Group\Models\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use User\Models\User;

/**
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $content
 * @property Group $group
 * @property User $user
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Message extends Model
{
    use SoftDeletes;

    protected $table = 'messages';
    protected $casts = [
        'group_id' => 'int',
        'user_id' => 'int'
    ];
    protected $with = ['user', 'group'];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return HasOne
     */
    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }
}
