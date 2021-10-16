<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

/**
 * Class Group
 * @package App\Models
 *
 * int $group_id
 * int $user_id
 * Carbon|null $joined_at
 * Carbon|null $created_at
 * Carbon|null $updated_at
 */
class GroupUser extends Model
{
    protected $dates = ['joined_at', 'created_at', 'updated_at'];
}
