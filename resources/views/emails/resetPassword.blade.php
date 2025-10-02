<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe</title>
</head>

<body style="margin: 100px;">

    <h3>Hi.. <span style="color:cornflowerblue">{{ $userName }}</span></h3>
    <h4>Vous avez demandé à réinitialiser votre mot de passe</h4>
    <hr>
    <h1 style="font-weight: bold;color:cornflowerblue "><a
            href="{{env('APP_FRONT_URL')}}/admin/auth/recovery-password/{{ $token }}">Cliquez ici pour
            Réinitialiser le mot de passe</a></h1>
    <br><br>
</body>

</html>