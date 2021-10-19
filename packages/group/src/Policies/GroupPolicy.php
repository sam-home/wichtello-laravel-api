<?php

namespace Group\Policies;

use Group\Models\Group;
use User\Models\User;
use Illuminate\Auth\Access\Response;

class GroupPolicy
{
    public function update(User $user, Group $group): Response
    {
        return $user->id === $group->user_id
            ? Response::allow()
            : Response::deny('Du hast die Gruppe nicht erstellt.');
    }
}
