@extends('emails.base')
@section('body-content')
<p>Cher(e) {{ $user?->name }},</p>

<p>Vous avez demandé à rattacher le centre <strong>{{ $requete->name }}</strong>
   à votre compte promoteur afin de régulariser son dossier sur la plateforme.</p>

<p>Voici votre code de confirmation :</p>
<p><strong>{{ $code }}</strong></p>

<p>Ce code est valable pendant {{ $minutes }} minutes. Ne le partagez avec personne.</p>
<p>Si vous n'êtes pas à l'origine de cette demande, ignorez simplement ce message :
   aucun rattachement ne sera effectué sans ce code.</p>

<p class="text-center">Cordialement,</p>
<p class="text-center">L'équipe DFEA</p>
@endsection
