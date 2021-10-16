<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PollOption
 * @package App\Models
 * @property int $user_id
 * @property int $option_id
 */
class PollUserOption extends Model
{
    protected $table = 'poll_user_options';

    public function option()
    {
        return $this->hasOne(PollOption::class, 'id', 'option_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
