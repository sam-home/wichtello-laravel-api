<?php

namespace User\Controllers;

use User\Models\User;
use User\Services\UserService;

class UserController
{
    public function __construct(public UserService $userService) {
    }

    public function me(): User
    {
        return $this->userService->getAuthenticatedUser();
    }

    public function authenticate(): User
    {
        return $this->userService->getAuthenticatedUser();
    }

    public function premium(): User
    {
        $user = $this->userService->getAuthenticatedUser();
        return $this->userService->setPremium($user, true);
    }
}
