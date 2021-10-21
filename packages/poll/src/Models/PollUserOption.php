<?php

namespace Poll\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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
class PollUserOption extends Model
{
    use SoftDeletes;

    protected $table = 'poll_user_options';
    protected $casts = [
        'group_id' => 'int',
        'user_id' => 'int'
    ];
}
