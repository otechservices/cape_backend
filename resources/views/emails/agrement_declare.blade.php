@extends('emails.base')
@section('body-content')
<p>Cher(e) {{ $nom }},</p>

<p>La DFEA confirme que le centre <strong>{{ $requete->name }}</strong> (dossier {{ $requete->code }})
   est agréé{{ $requete->aggreement_reference ? ' sous la référence '.$requete->aggreement_reference : '' }}.
   Son statut est désormais « Agréé » sur la plateforme : il n'a pas à passer en session.</p>
@if ($pieceJointe)
    <p>Vous trouverez l'agrément en pièce jointe.</p>
@endif

@if ($invitationUrl)
    <p>Aucun compte promoteur n'est encore associé à ce centre. Créez le vôtre à l'aide du lien
       ci-dessous : le centre y sera automatiquement rattaché.</p>
    <p class="text-center"><a href="{{ $invitationUrl }}">Créer mon compte promoteur</a></p>
    <p>Ce lien est personnel et valable 7 jours, jusqu'au {{ $expiration->format('d/m/Y à H:i') }}.
       Passé ce délai, rapprochez-vous de la DFEA pour en recevoir un nouveau.</p>
    <p>Une fois votre compte créé, merci de renseigner depuis votre espace promoteur :</p>
@else
    <p>Merci de renseigner dès à présent, depuis votre espace promoteur :</p>
@endif

<ul>
    <li><a href="{{ $espaceUrl }}/staff">le personnel du centre</a> ;</li>
    <li><a href="{{ $espaceUrl }}/residents">les pensionnaires accueillis</a> ;</li>
    <li><a href="{{ $espaceUrl }}/activity-report">les rapports d'activité</a>.</li>
</ul>

<p class="text-center">Cordialement,</p>
<p class="text-center">L'équipe DFEA</p>
@endsection
