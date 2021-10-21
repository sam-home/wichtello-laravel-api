<?php

namespace Poll\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use User\Models\User;

/**
 * @property int $id
 * @property int $poll_id
 * @property int $user_id
 * @property string $content
 * @property ?Poll $poll
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class PollOption extends Model
{
    use SoftDeletes;

    protected $table = 'poll_options';
    protected $casts = [
        'group_id' => 'int',
        'user_id' => 'int'
    ];

    protected $appends = ['has_voted', 'count'];

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class, 'id', 'poll_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'poll_user_options', 'poll_option_id', 'user_id');
    }

    public function getHasVotedAttribute(): bool
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function getCountAttribute(): int
    {
        return $this->users()->count();
    }
}
