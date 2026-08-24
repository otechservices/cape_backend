@extends('emails.base')
@section('body-content')
<p>Cher(e) Monsieur/Madame,</p>

@if ($approved)
    <p>L'agrément du centre <strong>{{ $requete->name }}</strong> a été reconnu par
       la DFEA. Le centre est désormais enregistré comme autorisé sur la plateforme.</p>
    <p>Vous trouverez votre agrément en pièce jointe, ainsi que, le cas échéant,
       les identifiants d'accès de votre centre.</p>
@else
    <p>Après examen, la DFEA n'a pas reconnu l'agrément déclaré pour le centre
       <strong>{{ $requete->name }}</strong>.</p>
    @if ($observation)
        <p>Motif : {{ $observation }}</p>
    @endif
    <p>Votre dossier reste accessible depuis votre espace promoteur, où vous
       pouvez suivre la suite donnée à votre demande.</p>
@endif

<p class="text-center">Cordialement,</p>
<p class="text-center">L'équipe DFEA</p>
@endsection
