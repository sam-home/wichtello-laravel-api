<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Broadcast
 * @package App\Models
 *
 * @property int $id
 * @property string $event
 * @property Collection<string> $channels
 * @property Collection $payload
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Broadcast extends Model
{
    protected $table = 'broadcasts';
    protected $hidden = ['event', 'created_at', 'updated_at'];
    protected $casts = [
        'channels' => 'array',
        'payload' => 'array'
    ];
}
