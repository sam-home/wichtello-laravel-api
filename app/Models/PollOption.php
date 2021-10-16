<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PollOption
 * @package App\Models
 * @property int $id
 * @property int $poll_id
 * @property string $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PollOption extends Model
{
    protected $table = 'poll_options';
    protected $appends = ['has_voted', 'count'];


    public function users()
    {
        return $this->belongsToMany(PollOption::class, 'poll_user_options', 'option_id', 'user_id');
    }

    public function getHasVotedAttribute()
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function getCountAttribute()
    {
        return $this->users()->count();
    }
}
