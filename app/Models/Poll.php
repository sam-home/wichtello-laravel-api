<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Poll
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property int $group_id
 * @property string $name
 * @property string $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Poll extends Model
{
    protected $table = 'polls';

    protected $appends = ['has_voted', 'count', 'is_own'];

    public function options()
    {
        return $this->hasMany(PollOption::class, 'poll_id', 'id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function group()
    {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }

    public function getHasVotedAttribute()
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        $optionIds = PollOption::query()->where('poll_id', $this->id)->pluck('id');

        return PollUserOption::query()->whereIn('option_id', $optionIds)->where('user_id', $user->id)->exists();
    }

    public function getCountAttribute()
    {
        $optionIds = PollOption::query()->where('poll_id', $this->id)->pluck('id');

        return PollUserOption::query()->whereIn('option_id', $optionIds)->count();
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
