<?php

namespace Wish\Controllers;

use Group\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use User\Models\User;
use Wish\Models\Wish;
use Wish\Services\WishService;

class WishController
{
    public function __construct(public WishService $wishService) {
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function index(Group $group): Collection
    {
        return $this->wishService->index($group);
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

        /** @var User $user */
        $user = auth()->user();

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
}
