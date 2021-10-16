@extends('layouts.app')

@section('content')
<div class="container">
    <div class="view-box">
        <h1>Gruppe "{{ $group->name }}" beitreten</h1>

        <p class="lead">
            {{ $group->description }}
        </p>

        <div class="form-group">
            <label class="radio-inline"><input type="radio" name="mode" value="register">Registrieren</label>
            <label class="radio-inline"><input type="radio" name="mode" value="login">Anmelden</label>
        </div>

        <form id="register_form" action="{{ route('join') }}" method="post">
            <input type="hidden" name="hash" value="{{ $hash }}">
            <input type="hidden" name="mode" value="register">
            <div class="form-group">
                <label for="">Name</label>
                <input type="text" name="name" class="form-control">
            </div>

            <div class="form-group">
                <label for="">E-Mail</label>
                <input type="email" name="email" class="form-control">
            </div>

            <div class="form-group">
                <label for="">Passwort</label>
                <input type="password" name="password" class="form-control">
            </div>

            <div class="form-group">
                <label for="">Passwort (bestätigen)</label>
                <input type="password" name="password_confirm" class="form-control">
            </div>

            <div class="form-group">
                <button class="btn btn-primary btn-block">Registrieren und beitreten</button>
            </div>
        </form>

        <form id="login_form" action="{{ route('join') }}" method="post">
            <input type="hidden" name="hash" value="{{ $hash }}">
            <input type="hidden" name="mode" value="login">
            <div class="form-group">
                <label for="">E-Mail</label>
                <input type="email" name="email" class="form-control">
            </div>

            <div class="form-group">
                <label for="">Passwort</label>
                <input type="password" name="password" class="form-control">
            </div>

            <div class="form-group">
                <button class="btn btn-primary btn-block">Beitreten</button>
            </div>
        </form>
    </div>
</div>
@endsection