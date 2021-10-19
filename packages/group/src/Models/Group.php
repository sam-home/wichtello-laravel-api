<?php

namespace Group\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use User\Models\User;

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

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_users', 'group_id', 'user_id')
            ->withPivot(['joined_at', 'is_admin']);
    }
}
