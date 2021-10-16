<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientEvent extends Model
{
    protected $table = 'client_events';

    public function scopeSendGroup($query, $group, $name, $object) {
        $clientEvent = new ClientEvent();
        $clientEvent->group_id = $group->id;
        $clientEvent->name = $name;
        $clientEvent->content = $object->id;
        $clientEvent->save();
    }

    public function scopeSendUser($query, $user, $name, $object) {
        $clientEvent = new ClientEvent();
        $clientEvent->user_id = $user->id;
        $clientEvent->name = $name;
        $clientEvent->content = $object->id;
        $clientEvent->save();
    }
}
