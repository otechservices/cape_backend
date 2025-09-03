<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<body>

Bonjour Monsieur/Madame {{$member->firstname}} {{$member->lastname}},
<br>

<br>
Vous êtes invité à participer à la session de validation des dossiers CAPE.
<br>
    Veuillez accéder à l'espace de traitement en ligne en cliquant sur le lien suivant: <a href="{{env('APP_FRONT_URL')}}/admin/auth/login">Plateforme numérique en ligne de gestion des centres d'accueil et de protection de l'enfant en République du Bénin</a>
    <br>
    Veuillez vous servir des identifiants suivants pour vous connecter :

        <ul>
            <li><strong> Email: </strong> {{$member->email}} </li>
            <li><strong> Mot de passe: </strong> {{$password}} </li>
        </ul>
    Cordialement,


    
</body>
</html>