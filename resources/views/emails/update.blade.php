<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<body>

Bonjour cher promoteur de centre,
<br>

Votre dossier nécessite une mise à jour.
<br>

Motif : <br>
{{$motif}}
<br>

Pour rappel, le code de votre demande est {{$code ??"Code"}}

<br>

Nous vous informons que tout soumission de dossier mise en attente pour complément doit avoir une retour sous jour(s) sans quoi, elle est annulée.
Vous procéderez donc à une nouvelle demande.
<br>

Veuillez cliquer sur le lien suivant pour nécessaire à faire:
<a href="{{env('APP_FRONT_URL')}}/requete/updating/{{$token}}/{{$code}}">Page de mise à jour </a>

Cordialement,


    
</body>
</html>