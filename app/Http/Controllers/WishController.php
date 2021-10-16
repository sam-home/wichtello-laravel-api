<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Wish;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishController extends Controller
{
    public function index(Group $group)
    {
        $user = $this->getAuthenticatedUser();

        return Wish::query()
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->get();
    }

    public function get(Group $group, Wish $wish)
    {
        if ($wish->group_id !== $group->id) {
            return $this->error();
        }

        return $wish;
    }

    /**
     * @param Group $group
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Group $group, Request $request)
    {
        $this->validate($request, [
            'content' => 'required'
        ]);

        $user = $this->getAuthenticatedUser();

        $content = $request->get('content');

        $wish = new Wish();
        $wish->group_id = $group->id;
        $wish->user_id = $user->id;
        $wish->content = $content;
        $wish->save();

        return $this->success(['wish' => $wish]);
    }

    /**
     * @param Group $group
     * @param Wish $wish
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Group $group, Wish $wish, Request $request)
    {
        if ($group->id !== $wish->group_id) {
            return $this->error();
        }

        $this->validate($request, [
            'content' => 'required'
        ]);

        $content = $request->get('content');

        $wish->content = $content;
        $wish->save();

        return $this->success(['wish' => $wish]);
    }

    /**
     * @param Group $group
     * @param Wish $wish
     * @return JsonResponse
     * @throws Exception
     */
    public function remove(Group $group, Wish $wish)
    {
        if ($group->id !== $wish->group_id) {
            return $this->error();
        }

        $wish->delete();

        return $this->success();
    }
}
