@extends('emails.base')

@section('body-content')

<p>Bonjour <strong>{{$req?->name_promoter ?? 'cher promoteur'}}</strong>,</p>

<p>
    Nous avons bien reçu votre dossier de demande d'autorisation
    <strong>(Réf. : {{$code ?? '—'}})</strong>.
    Après examen, il nécessite un complément d'information avant de poursuivre son instruction.
</p>

<p><strong>Motif de la mise en attente :</strong></p>

<div style="
    background-color: #fffbeb;
    border-left: 4px solid #f59e0b;
    border-radius: 4px;
    padding: 16px 20px;
    margin: 12px 0;
    font-size: 14px;
    line-height: 1.6;
">
    {!! $motif !!}
</div>

<p>
    Nous vous invitons à vous connecter à la plateforme afin de mettre à jour votre dossier
    dans les meilleurs délais. Passé ce délai sans retour de votre part, la demande sera automatiquement annulée
    et vous devrez procéder à une nouvelle soumission.
</p>

<p style="margin-top: 20px;">
    @if($req?->type_cape_id == 1)
        <a href="{{ env('APP_FRONT_URL') }}/promoter/inscription-cape/{{$code}}"
           style="
               display: inline-block;
               background-color: #1e3a5f;
               color: #ffffff;
               padding: 10px 22px;
               border-radius: 5px;
               text-decoration: none;
               font-weight: bold;
               font-size: 14px;
           ">
            Mettre à jour mon dossier
        </a>
    @else
        <a href="{{ env('APP_FRONT_URL') }}/promoter/inscription-garderie/{{$code}}"
           style="
               display: inline-block;
               background-color: #1e3a5f;
               color: #ffffff;
               padding: 10px 22px;
               border-radius: 5px;
               text-decoration: none;
               font-weight: bold;
               font-size: 14px;
           ">
            Mettre à jour mon dossier
        </a>
    @endif
</p>

<p style="margin-top: 24px;">Cordialement,<br><strong>L'équipe CAPE</strong></p>

@endsection
