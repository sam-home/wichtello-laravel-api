<?php

namespace Wish\Controllers;

use Group\Models\Group;
use Group\Services\GroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use User\Models\User;
use User\Services\UserService;
use Wish\Models\Wish;
use Wish\Services\WishService;

class WishController
{
    public function __construct(protected WishService $wishService, protected UserService $userService) {
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function index(Group $group): Collection
    {
        $user = $this->userService->getAuthenticatedUser();
        return $this->wishService->index($group, $user);
    }

    /**
     * @param Group $group
     * @param Wish $wish
     * @return Wish
     */
    public function get(Group $group, Wish $wish): Wish
    {
        return $wish;
    }

    /**
     * @param Group $group
     * @param Request $request
     * @return Wish
     */
    public function store(Group $group, Request $request): Wish
    {
        $input = $request->validate([
            'content' => 'required'
        ]);

        $user = $this->userService->getAuthenticatedUser();

        return $this->wishService->store($group, $user, $input['content']);
    }

    /**
     * @param Group $group
     * @param Wish $wish
     * @param Request $request
     * @return Wish
     */
    public function update(Group $group, Wish $wish, Request $request): Wish
    {
        $input = $request->validate([
            'content' => 'required'
        ]);

        return $this->wishService->update($wish, $input['content']);
    }

    /**
     * @param Group $group
     * @param Wish $wish
     */
    public function destroy(Group $group, Wish $wish)
    {
        $this->wishService->destroy($wish);
    }

    public function getPartnerWishes(Group $group)
    {
        $user = $this->userService->getAuthenticatedUser();
        return $this->wishService->getPartnerWishes($group, $user);
    }
}
