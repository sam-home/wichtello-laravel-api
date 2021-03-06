<?php

namespace User\Tests\Feature;

use Group\Services\GroupService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use User\Services\UserService;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected GroupService $groupService;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app()->make(UserService::class);
    }

    public function testInvalidCredentials()
    {
        $this->userService->store('John Doe', 'john.doe@example.org', 'secret', true);

        $credentials = 'Basic ' . base64_encode('john.doe@example.org:wrong-secret');

        $this->withHeader('Authorization', $credentials)->post('/users/authenticate')
            ->assertStatus(401);
    }

    public function testMe()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret', true);

        $this->actingAs($user)->put('/users/me', ['name' => 'Jack'])
            ->assertJson([
                'name' => 'Jack'
            ])
            ->assertStatus(200);
    }

    public function testAuthenticate()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret', true);

        $this->actingAs($user)->post('/users/authenticate')
            ->assertJson([
                'name' => 'John Doe',
                'email' => 'john.doe@example.org'
            ])
            ->assertStatus(200);
    }

    public function testAddPremium()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret', true);

        $this->actingAs($user)->post('/users/premium')
            ->assertJson([
                'name' => 'John Doe',
                'premium' => true
            ])
            ->assertStatus(200);
    }

    public function testRegister()
    {
        $this->post('/users/register', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.org',
            'password' => 'secret123',
            'password_confirm' => 'secret123'
        ])
            ->assertStatus(200);
    }

    public function testReset()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret', true);

        $this->actingAs($user)->post('/users/reset', ['email' => 'john.doe@example.org'])
            ->assertStatus(200);
    }

    public function testChange()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret', true);

        $this->userService->reset($user->email);

        $user->refresh();

        $this->actingAs($user)->post('/users/change', ['code' => $user->reset, 'password' => 'secret123'])
            ->assertStatus(200);
    }

    public function testVerify()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret');

        $this->actingAs($user)->post('/users/verify', ['confirm' => $user->confirm])
            ->assertStatus(200);
    }
}
