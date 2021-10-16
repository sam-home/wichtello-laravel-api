<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

/**
 * Class Group
 * @package App\Models
 *
 * @property int $id
 * @property int $user_id
 * @property string $state
 * @property string $name
 * @property string $description
 * @property string $join_link
 * @property Collection<User> $users
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Group extends Model
{
    use SoftDeletes, Notifiable;

    protected $table = 'groups';

    protected $appends = ['is_admin'];

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_users', 'group_id', 'user_id')
            ->withPivot(['joined_at', 'is_admin']);
    }

    /**
     * @return HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'group_id', 'id');
    }

    public function polls()
    {
        return $this->hasMany(Poll::class, 'group_id', 'id');
    }

    public function wishes()
    {
        return $this->hasMany(Wish::class, 'group_id', 'id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'group_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function partners()
    {
        return $this->hasMany(Partner::class, 'group_id', 'id');
    }

    public function getIsAdminAttribute()
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return $this->users()->where('user_id', $user->id)->wherePivot('is_admin', 1)->exists();
    }
}
