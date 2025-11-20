@extends('emails.base')
@section('body-content')
    <p>Vous avez un nouveau message depuis le formulaire de contact :</p>
    <p><strong>Nom :</strong> {{ $data['name'] }}</p>
    <p><strong>Email :</strong> {{ $data['email'] }}</p>
    <p><strong>Sujet :</strong> {{ $data['sujet'] }}</p>
    <p><strong>Téléphone :</strong> {{ $data['phone'] }}</p>
    <p><strong>Message :</strong> {{ $data['observation'] }}</p>
@endsection