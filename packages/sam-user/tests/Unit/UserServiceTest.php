<?php

namespace Sam\User\Tests\Unit;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Sam\User\Exceptions\ConfirmException;
use Sam\User\Exceptions\ResetException;
use Sam\User\Services\UserService;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
    }

    public function testStoreInactiveUser()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'start');

        $this->assertNotNull($user);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.org',
            'active' => false
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john.doe@example.org', $user->email);
        $this->assertNotNull($user->confirm);
        $this->assertTrue($this->userService->checkPassword($user, 'start'));
    }

    public function testStoreActiveUser()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'start', true);

        $this->assertNotNull($user);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.org',
            'active' => true,
            'confirm' => null
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john.doe@example.org', $user->email);
        $this->assertTrue($this->userService->checkPassword($user, 'start'));
    }

    public function testUpdate()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'start');
        $user = $this->userService->update($user, [
            'name' => 'Jane Doe',
            'password' => 'secret'
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Jane Doe'
        ]);

        $this->assertEquals('Jane Doe', $user->name);
        $this->assertTrue($this->userService->checkPassword($user, 'secret'));
    }

    public function testDelete()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'start');

        $this->userService->destroy($user);

        $this->assertDatabaseMissing('users', [
            'name' => 'John Doe'
        ]);
    }

    /**
     * @throws ConfirmException
     */
    public function testActivateUser()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'start');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'active' => false
        ]);

        $this->userService->activate($user->confirm);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'active' => true,
            'confirm' => null
        ]);
    }

    /**
     * @throws ResetException
     */
    public function testResetPassword()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'start', true);

        $this->userService->resetPassword('john.doe@example.org');

        $this->assertNotNull($user->reset);

        $user = $this->userService->confirmPassword($user->reset, 'secret');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'reset' => null
        ]);

        $this->assertNull($user->reset);

        $this->assertTrue($this->userService->checkPassword($user, 'secret'));
    }

    public function testAuthenticate()
    {
        $unauthenticatedUser = $this->userService->getAuthenticatesUser();
        $this->assertNull($unauthenticatedUser);

        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'start', true);

        $this->userService->authenticate($user);

        $authenticatedUser = $this->userService->getAuthenticatesUser();
        $this->assertNotNull($authenticatedUser);
    }
}
