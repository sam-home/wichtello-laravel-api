<?php

namespace User\Controllers;

use Illuminate\Http\Request;
use User\Models\User;
use User\Services\UserService;

class UserController
{
    public function __construct(public UserService $userService) {
    }

    public function me(Request $request): User
    {
        $input = $request->validate([
            'name' => 'sometimes',
            'password' => 'sometimes'
        ]);

        $user = $this->userService->getAuthenticatedUser();
        return $this->userService->update($user, $input);
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
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'password_confirm' => 'required'
        ]);

        $this->userService->store($input['name'], $input['email'], $input['password']);
    }

    public function reset(Request $request)
    {
        $input = $request->validate([
            'email' => 'required',
        ]);

        $this->userService->reset($input['email']);
    }

    public function change(Request $request)
    {
        $input = $request->validate([
            'code' => 'required',
            'password' => 'required'
        ]);

        $this->userService->change($input['code'], $input['password']);
    }

    public function verify(Request $request)
    {
        $input = $request->validate([
            'confirm' => 'required'
        ]);

        $this->userService->verify($input['confirm']);
    }
}
