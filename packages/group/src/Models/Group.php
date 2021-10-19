<?php

namespace Group\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use User\Models\User;
use User\Services\UserService;

/**
 * @property int $id
 * @property int $user_id
 * @property string $state // invite, start
 * @property string $name
 * @property string $join_link
 * @property string $description
 * @property Collection $users
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

    protected $appends = ['is_admin'];

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_users', 'group_id', 'user_id')
            ->withPivot(['joined_at', 'is_admin']);
    }

    /**
     * @return bool
     * @throws BindingResolutionException
     */
    public function getIsAdminAttribute(): bool
    {
        /** @var UserService $userService */
        $userService = app()->make(UserService::class);
        $user = $userService->getAuthenticatedUser();

        if ($user === null) {
            return false;
        }

        return GroupUser::query()
            ->where('group_id', $this->id)
            ->where('user_id', $user->id)
            ->where('is_admin', true)
            ->exists();
    }
}
