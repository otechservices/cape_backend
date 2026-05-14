@extends('emails.base')

@section('body-content')

<p>Cher promoteur,</p>

<p>
    Nous avons le regret de vous informer que votre dossier de demande d'autorisation
    n'a pas obtenu l'autorisation à l'issue de la session de validation.
</p>

<p><strong>Motif du rejet :</strong></p>

<div style="
    background-color: #fff1f2;
    border-left: 4px solid #ef4444;
    border-radius: 4px;
    padding: 16px 20px;
    margin: 12px 0;
    font-size: 14px;
    line-height: 1.6;
">
    {!! $motif !!}
</div>

<p>
    Pour toute question relative à cette décision, nous vous invitons à contacter
    le service compétent aux coordonnées mentionnées ci-dessous.
</p>

<p style="margin-top: 24px;">Cordialement,<br><strong>L'équipe CAPE</strong></p>

@endsection
