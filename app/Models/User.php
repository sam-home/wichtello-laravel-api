<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'confirm', 'password_reset'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_users', 'user_id', 'group_id')
            ->withPivot(['joined_at']);
    }

    public function wishes()
    {
        return $this->hasMany(Wish::class, 'user_id', 'id');
    }

    public function partners()
    {
        return $this->hasMany(Partner::class, 'user_id', 'id');
    }

    public function hasPartnerInGroup($partnerId, $groupId) {
        return $this->partners()->whereGroupId($groupId)->wherePartnerId($partnerId)->exists();
    }
}
