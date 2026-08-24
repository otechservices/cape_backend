@extends('emails.base')
@section('body-content')
<p>À l'attention du centre <strong>{{ $requete->name }}</strong>,</p>

<p>Une demande de rattachement de votre centre vient d'être déposée sur la
   plateforme par <strong>{{ $user?->name }}</strong> ({{ $user?->email }}),
   en vue de régulariser votre dossier et d'y déposer vos pièces justificatives.</p>

<p>Si cette demande émane bien de votre promoteur, aucune action n'est requise de
   votre part : la DFEA examinera le dossier avant toute validation.</p>

<p>Dans le cas contraire, signalez-le sans délai à l'adresse
   <a href="mailto:masm.dfea@gouv.bj">masm.dfea@gouv.bj</a>.</p>

<p class="text-center">Cordialement,</p>
<p class="text-center">L'équipe DFEA</p>
@endsection
