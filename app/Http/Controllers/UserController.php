<?php

namespace App\Http\Controllers;

use App\Mail\ForgetPasswordConfirmEmail;
use App\Mail\ForgetPasswordEmail;
use App\Mail\RegisterEmail;
use App\Models\ClientEvent;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function authenticate()
    {
        $user = auth()->user();
        $user->token = sha1(time() . uniqid() . rand());
        $user->save();

        return $user;
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        if ($request->has('name')) {
            $user->name = $request->get('name');
        }

        if ($request->has('password')) {
            $password = $request->get('password');
            if (strlen($password) > 0) {
                $user->password = bcrypt($request->get('password'));
            }
        }

        $user->save();

        return ['success' => true];
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'name' => 'required',
            'password' => 'required'
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
        $user->active = false;
        $user->save();

        Mail::to($email)->queue(new RegisterEmail($user, $password, $confirm));

        return ['success' => true];
    }

    public function registerConfirm(Request $request)
    {
        $request->validate([
            'confirm' => 'required'
        ]);

        $confirm = $request->get('confirm');

        $user = User::whereConfirm($confirm)->first();
        if ($user === null) {
            return 'Fehler beim Aktivieren des Accounts.';
        }

        $user->active = true;


        if ($user->join_group !== null) {
            $group = Group::whereId($user->join_group)->first();

            if ($group !== null) {
                if (!$group->users->contains($user->id)) {
                    $group->users()->attach($user->id, ['joined_at' => Carbon::now()]);

                    ClientEvent::sendGroup($group, 'joined:user', $user);

                    $user->join_group = null;
                }
            }
        }

        $user->save();

        return view('confirmed');
    }

    public function forgetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->get('email');

        $passwordReset = hash('sha256', $email . uniqid());

        $user = User::whereEmail($email)->firstOrFail();
        $user->password_reset = $passwordReset;
        $user->save();

        Mail::to($user->email)->queue(new ForgetPasswordEmail($user));

        return ['success' => true];
    }

    public function forgetPasswordConfirm(Request $request)
    {
        $request->validate([
            'password_reset' => 'required|exists:users'
        ]);

        $passwordReset = $request->get('password_reset');

        $user = User::wherePasswordReset($passwordReset)->firstOrFail();

        $password = strtoupper(substr(sha1($passwordReset), 0, 8));

        $user->password = bcrypt($password);
        $user->save();

        Mail::to($user->email)->queue(new ForgetPasswordConfirmEmail($user, $password));

        return 'Dir wurde soeben ein neues Passwort zugeschickt.';
    }
}
