<?php

namespace Poll\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use User\Models\User;

/**
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $name
 * @property string $description
 * @property Collection $options
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Poll extends Model
{
    use SoftDeletes;

    protected $table = 'polls';
    protected $casts = [
        'group_id' => 'int',
        'user_id' => 'int'
    ];
    protected $appends = ['has_voted', 'is_own', 'count'];

    /**
     * @return HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class, 'poll_id', 'id');
    }

    /**
     * @return bool
     */
    public function getHasVotedAttribute(): bool
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        $optionIds = PollOption::query()->where('poll_id', $this->id)->pluck('id');

        return PollUserOption::query()->whereIn('poll_option_id', $optionIds)->where('user_id', $user->id)->exists();
    }

    /**
     * @return int
     */
    public function getCountAttribute(): int
    {
        $optionIds = PollOption::query()->where('poll_id', $this->id)->pluck('id');

        return PollUserOption::query()->whereIn('poll_option_id', $optionIds)->count();
    }

    /**
     * @return bool
     */
    public function getIsOwnAttribute(): bool
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return $this->user_id === $user->id;
    }
}
