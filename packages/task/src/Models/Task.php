<?php

namespace Task\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use User\Models\User;

/**
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $name
 * @property string $description
 * @property int $slots
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Task extends Model
{
    use SoftDeletes;

    protected $table = 'tasks';
    protected $casts = [
        'group_id' => 'int',
        'user_id' => 'int',
        'slots' => 'int'
    ];
    protected $with = ['user', 'users'];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_users', 'task_id', 'user_id')
            ->withPivot(['comment']);
    }
}
