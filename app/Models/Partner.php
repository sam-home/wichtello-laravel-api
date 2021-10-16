<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $table = 'partners';

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'partner_id');
    }
}
