<?php

namespace Sam\User\Controllers;

use Sam\User\Services\UserService;

class UserController
{
    public function __construct(public UserService $userService) {
    }

    /**
     * @return string[]
     */
    public function index(): array
    {
        return ['nice'];
    }
}
