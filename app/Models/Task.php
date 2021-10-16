<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Task
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property int $group_id
 * @property string $name
 * @property string $description
 * @property int $slots
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Task extends Model
{
    protected $table = 'tasks';
    protected $with = ['users'];
    protected $appends = ['signed_in', 'user_count', 'is_own'];


    public function users()
    {
        return $this->belongsToMany(User::class, 'task_users', 'task_id', 'user_id')
            ->withPivot(['comment']);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function group()
    {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }

    public function getSignedInAttribute()
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->users()->whereUserId(auth()->user()->id)->exists();
    }

    public function getUserCountAttribute()
    {
        return $this->users()->count();
    }

    public function getIsOwnAttribute()
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return $this->user_id === $user->id;
    }
}
