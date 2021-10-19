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

    public function testMe()
    {
        $user = $this->userService->store('John Doe', 'john.doe@example.org', 'secret', true);

        $this->actingAs($user)->get('/users/me')
            ->assertJson([
                'name' => 'John Doe'
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
}
