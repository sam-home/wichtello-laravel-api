<?php

namespace Poll\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class, 'id', 'poll_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'poll_user_options', 'poll_option_id', 'user_id');
    }
}
