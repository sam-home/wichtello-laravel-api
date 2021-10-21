Hallo {{ $user->name }},

<p>
    es wurde soeben ein Account für dich erstellt. Deine Benutzerdaten sind:
</p>

<p>
    E-Mail: {{ $user->email }}<br>
    Password: {{ $password }}
</p>

<p>
    Um den Account zu aktivieren klicke auf den folgenden den <a href="{{ env('APP_URL') }}/users/confirm?confirm={{ $user->confirm }}">Link</a>.
</p>

<p>
    Fall du dir keinen Account erstellt haben solltest, dann ignorier diese E-Mail.
</p>
