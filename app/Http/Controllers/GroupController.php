<?php

namespace App\Http\Controllers;

use App\Mail\RegisterJoinEmail;
use App\Models\ClientEvent;
use App\Models\Group;
use App\Models\User;
use App\Models\Wish;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class GroupController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return $user->groups;
    }

    public function single(Group $group)
    {
        return $group;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $user = auth()->user();

        $group = new Group();
        $group->user_id = auth()->user()->id;
        $group->name = $request->get('name');
        $group->description = $request->get('description') ?? '';
        $group->state = 'invite';
        $group->save();

        $group->users()->attach($user->id, [
            'joined_at' => Carbon::now()
        ]);

        return $group;
    }

    public function update(Group $group, Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        if ($group->user_id !== auth()->user()->id) {
            return ['success' => false];
        }

        $group->name = $request->get('name');
        $group->description = $request->get('description');
        $group->save();

        ClientEvent::sendGroup($group, 'update:group', $group);

        return ['success' => true];
    }

    public function invite(Group $group, Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->get('email');
        $user = User::whereEmail($email)->first();

        if ($user === null) {
            $user = new User();
            $user->email = $email;
            $user->save();
        }

        if (!$group->users->contains($user->id)) {
            $group->users()->attach($user->id);

            ClientEvent::sendGroup($group, 'invite:user', $user);
        }

        return ['success' => true];
    }

    public function kickUser(Group $group, User $user)
    {
        if ($group->user_id !== auth()->user()->id) {
            return ['success' => false];
        }

        if ($group->users->contains($user->id)) {
            $group->users()->detach($user->id);
        }

        return ['success' => true];
    }

    public function leave(Group $group)
    {
        if ($group->user_id === auth()->user()->id) {
            return ['success' => false];
        }

        if ($group->users->contains(auth()->user()->id)) {
            $group->users()->detach(auth()->user()->id);
        }

        return ['success' => true];
    }

    public function decline(Group $group)
    {
        if ($group->users->contains(auth()->user()->id)) {
            $group->users()->detach(auth()->user()->id);

            ClientEvent::sendGroup($group, 'decline:user', auth()->user());
        }

        return ['success' => true];
    }

    public function accept(Group $group)
    {
        if ($group->users->contains(auth()->user()->id)) {
            $user = $group->users()->whereUserId(auth()->user()->id)->first();

            $user->pivot->joined_at = Carbon::now();
            $user->pivot->save();

            ClientEvent::sendGroup($group, 'accept:user', auth()->user());
        }

        return ['success' => true];
    }

    public function delete(Group $group)
    {
        if ($group->user_id !== auth()->user()->id) {
            return ['success' => false];
        }
        $group->delete();

        return ['success' => true];
    }

    public function toggleJoin(Group $group)
    {
        if ($group->join_link === null) {
            $group->join_link = md5(time() . microtime() . $group->name . uniqid());
        } else {
            $group->join_link = null;
        }

        $group->save();

        return ['success' => true];
    }

    public function users(Group $group)
    {
        return $group->users;
    }

    public function user(Group $group, User $user)
    {
        return $group->users()->where('user_id', $user->id)->first();
    }

    public function partner(Group $group)
    {
        $user = auth()->user();
        $partner = $group->partners()->with(['user'])->where('user_id', $user->id)->first();

        if ($partner === null) {
            return ['user' => null];
        }

        $partner = $partner->user;

        if ($partner === null) {
            return ['user' => null];
        }

        $partner->wishes = Wish::query()->where('user_id', $partner->id)->where('group_id', $group->id)->get();

        return [
            'user' => $partner
        ];
    }

    protected function getPartnerIds($userIds)
    {
        $pickedIds = [];
        $parnterIds = [];

        if (sizeof($userIds) === 0) {
            return null;
        }

        if (sizeof($userIds) === 1) {
            return null;
        }

        foreach ($userIds as $userId) {
            $availableUsers = array_diff($userIds, $pickedIds);
            $availableUsers = array_diff($availableUsers, [$userId]);

            if (sizeof($availableUsers) === 0) {
                return $this->getPartnerIds($userIds);
            }

            $pickedId = $availableUsers[array_rand($availableUsers)];

            $parnterIds[] = $pickedId;

            $pickedIds[] = $pickedId;
        }

        return $parnterIds;
    }

    public function start(Group $group)
    {
        if ($group->user_id !== auth()->user()->id) {
            return ['success' => false];
        }

        $group->state = 'started';
        $group->save();

        $userIds = $group->users()->wherePivot('joined_at', '<>', null)->pluck('id')->toArray();
        $parnterIds = $this->getPartnerIds($userIds);

        if ($parnterIds === null) {
            return ['success' => false];
        }

        foreach ($userIds as $key => $userId) {
            DB::table('partners')->insert([
                ['group_id' => $group->id, 'user_id' => $userId, 'partner_id' => $parnterIds[$key]],
            ]);
        }

        return ['success' => true];
    }

    public function stop(Group $group)
    {
        if ($group->user_id !== auth()->user()->id) {
            return ['success' => false];
        }

        $group->state = 'invite';
        $group->save();

        DB::table('partners')->where('group_id', $group->id)->delete();

        return ['success' => true];
    }

    public function showJoin($hash)
    {
        $group = Group::whereJoinLink($hash)->first();

        if ($group === null) {
            return abort(404);
        }

        return view('join', ['group' => $group, 'hash' => $hash]);
    }

    public function join(Request $request)
    {
        $group = Group::whereJoinLink($request->get('hash'))->first();

        if ($group === null) {
            return abort(404);
        }

        if (!$request->has('mode')) {
            return 'bad request';
        }

        $mode = $request->get('mode');

        if ($mode === 'register') {
            $request->validate([
                'email' => 'required|email|unique:users',
                'name' => 'required',
                'password' => 'required',
                'password_confirm' => 'required|same:password'
            ]);

            $name = $request->get('name');
            $email = $request->get('email');
            $password = $request->get('password');

            $confirm = hash('sha256', $email . uniqid());

            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->password = bcrypt($password);
            $user->confirm = $confirm;
            $user->join_group = $group->id;
            $user->active = false;
            $user->save();

            Mail::to($email)->queue(new RegisterJoinEmail($user, $password, $confirm));

            return view('register');
        }

        if ($mode === 'login') {
            $request->validate([
                'email' => 'required',
                'password' => 'required'
            ]);

            $email = $request->get('email');
            $password = $request->get('password');

            $user = User::whereEmail($email)->first();

            if ($user !== null && Hash::check($password, $user->password)) {
                if (!$group->users->contains($user->id)) {
                    $group->users()->attach($user->id, ['joined_at' => Carbon::now()]);

                    ClientEvent::sendGroup($group, 'joined:user', $user);

                    return view('joined');
                }
            }
        }

        return abort(404);
    }

    public function setAdmin(Group $group, User $user)
    {
        DB::table('group_users')->where('group_id', $group->id)->where('user_id', $user->id)->update(['is_admin' => true]);

        return ['success' => true];
    }

    public function resetAdmin(Group $group, User $user)
    {
        DB::table('group_users')->where('group_id', $group->id)->where('user_id', $user->id)->update(['is_admin' => false]);

        return ['success' => true];
    }
}
