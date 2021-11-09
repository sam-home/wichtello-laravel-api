<p>Hallo {{ $user->name }},</p>

<p>für deinen Account wurde „Passwort vergessen“ ausgewählt. Du kannst diese E-Mail ignorieren, falls du dein Passwort nicht ändern willst.</p>

<p>Um dein Passwort endgültig zu ändern, folge diesen Schritten:</p>

<ul>
    <li>Öffnet die Wichtello App</li>
    <li>Gehe auf „Passwort ändern“</li>
    <li>Gebe deine E-Mail-Adresse an</li>
    <li>Füge den Code ein: <b>{{ $user->reset }}</b></li>
    <li>Gib dein neues Passwort zweimal ein</li>
    <li>Bestätige alles indem du auf „Ändern“ drückst</li>
</ul>

<p>Wenn alles geklappt hat, wirst du automatisch angemeldet.</p>

<p>
    Freundliche Grüße,<br>
    Wichtello
</p>
