@extends('emails.base')

@section('body-content')

@isset($code)
{{-- Contexte : validation du dossier numérique --}}

<p>Bonjour cher promoteur,</p>

<p>
    Nous avons le plaisir de vous informer que votre dossier de demande d'autorisation
    <strong>(Réf. : {{ $code }})</strong> a été <strong>validé</strong> par nos services.
</p>

@isset($motif)
@if($motif)
<p><strong>Observations :</strong></p>
<div style="
    background-color: #f0fdf4;
    border-left: 4px solid #22c55e;
    border-radius: 4px;
    padding: 16px 20px;
    margin: 12px 0;
    font-size: 14px;
    line-height: 1.6;
">
    {!! $motif !!}
</div>
@endif
@endisset

<p>
    Vous serez prochainement contacté pour la suite de la procédure.
    Veillez à rester disponible aux coordonnées enregistrées dans votre dossier.
</p>

@endisset

@isset($cps)
{{-- Contexte : invitation au dépôt du dossier physique --}}

<p>Bonjour cher promoteur,</p>

<p>
    Votre dossier numérique a été validé. Vous êtes invité à déposer le dossier physique
    auprès du <strong>Centre de Promotion Sociale : {{ $cps }}</strong>.
</p>

@isset($date_meeting)
<p>
    <strong>Date de passage :</strong>
    <span style="
        display: inline-block;
        background-color: #eff6ff;
        border: 1px solid #93c5fd;
        border-radius: 4px;
        padding: 4px 12px;
        font-weight: bold;
        color: #1e40af;
    ">
        {{ \Carbon\Carbon::parse($date_meeting)->translatedFormat('l d F Y') }}
    </span>
</p>
@endisset

<p>
    Merci de vous présenter muni de l'ensemble des pièces justificatives requises.
    Pour toute question, n'hésitez pas à contacter le service compétent.
</p>

@endisset

<p style="margin-top: 24px;">Cordialement,<br><strong>L'équipe CAPE</strong></p>

@endsection
