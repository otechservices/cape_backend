@extends('emails.base')
@section('body-content')
<p>Cher(e) {{ $user?->name }},</p>

<p>
    Votre mot de passe a été réinitialisé par un administrateur.
    Voici vos nouveaux identifiants de connexion :
</p>

<table style="border-collapse: collapse; margin: 16px 0;">
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Email :</td>
        <td style="padding: 6px 0;">{{ $user?->email }}</td>
    </tr>
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Nouveau mot de passe :</td>
        <td style="padding: 6px 0; font-family: monospace; font-size: 16px; letter-spacing: 1px;">{{ $password }}</td>
    </tr>
</table>

<p>
    Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe dès votre prochaine connexion.
</p>

<p>
    <a href="{{ env('APP_FRONT_URL') }}/admin/auth/login">Se connecter</a>
</p>

<p style="text-align:center;">Cordialement,</p>
<p style="text-align:center;">L'équipe CAPE</p>
@endsection
