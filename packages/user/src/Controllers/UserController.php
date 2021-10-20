<?php

namespace User\Controllers;

use Illuminate\Http\Request;
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

    public function register(Request $request)
    {
        $input = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'password_confirm' => 'required'
        ]);

        $this->userService->store($input['name'], $input['email'], $input['password']);
    }
}
