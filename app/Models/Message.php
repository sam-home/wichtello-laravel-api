<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Message
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property User|null $user
 * @property int $group_id
 * @property Group|null $group
 * @property string $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Message extends Model
{
    protected $table = 'messages';

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function group()
    {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }
}
