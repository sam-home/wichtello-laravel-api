<?php

namespace Sam\User\Services;

use Sam\User\Exceptions\ConfirmException;
use Sam\User\Exceptions\ResetException;
use Sam\User\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService {
    /**
     * @return User|null
     */
    public function getAuthenticatesUser(): ?User
    {
        /** @var User $user */
        $user = auth()->user();
        return $user;
    }

    /**
     * @param User $user
     */
    public function authenticate(User $user)
    {
        auth()->setUser($user);
    }

    /**
     * @param string $name
     * @param string $email
     * @param string $password
     * @param bool $active
     * @return User
     */
    public function store(string $name, string $email, string $password, bool $active = false): User
    {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->confirm = $active === true ? null : $this->generateRandomToken();
        $user->active = $active;
        $user->save();

        return $user;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function destroy(User $user): bool
    {
        return $user->delete();
    }

    /**
     * @param User $user
     * @param $password
     * @return bool
     */
    public function checkPassword(User $user, $password): bool
    {
        return Hash::check($password, $user->password);
    }

    /**
     * @return string
     */
    protected function generateRandomToken(): string
    {
        return hash('sha256', time() . uniqid() . rand());
    }

    /**
     * @param User $user
     * @return string
     */
    public function saveToken(User $user): string
    {
        $user->token = $this->generateRandomToken();
        $user->save();

        return $user->token;
    }

    /**
     * @param User $user
     * @param array $fields
     * @return User
     */
    public function update(User $user, array $fields): User
    {
        if (array_key_exists('name', $fields)) {
            $user->name = $fields['name'];
        }

        if (array_key_exists('password', $fields)) {
            if (strlen($fields['password']) > 0) {
                $user->password = Hash::make($fields['password']);
            }
        }

        $user->save();

        return $user;
    }

    /**
     * @param $confirm
     * @throws ConfirmException
     */
    public function activate($confirm): void
    {
        /** @var User $user */
        $user = User::query()->where('confirm', $confirm)->first();

        if ($user === null) {
            throw new ConfirmException();
        }

        $user->active = true;
        $user->confirm = null;

        $user->save();
    }

    /**
     * @param string $email
     * @return User
     */
    public function resetPassword(string $email): User
    {
        /** @var User $user */
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {

        }

        $user->reset = $this->generateRandomToken();
        $user->save();

        return $user;
    }

    /**
     * @param string $reset
     * @param string $password
     * @return User
     * @throws ResetException
     */
    public function confirmPassword(string $reset, string $password): User
    {
        /** @var User $user */
        $user = User::query()->where('reset', $reset)->first();

        if ($user === null) {
            throw new ResetException();
        }

        $user->reset = null;
        $user->password = Hash::make($password);
        $user->save();

        return $user;
    }
}
