@component('mail::message')
# {{$maildata['title']}}
Une demande de modification de mot de passe a récemment été faite sur votre compte.
Merci de cliquer sur le bouton ci dessous pour rénitialiser votre mot de passe


@component('mail::button', ['url' => $maildata['url']])
Réinitialiser
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
