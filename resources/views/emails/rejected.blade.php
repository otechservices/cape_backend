<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<body>

Cher promoteur de CAPE,
<br>
Votre Centre n'a pas reçu l'autorisation après la session de validation.
<br>
Veuillez prendre en compte ces différentes observations
<br>
<strong><u><b>Observation générale</b></u></strong>
<br>
{{$observation}}
<br>
<strong><u><b>Observations secondaires</b></u></strong>
<br>
<ol>
    @foreach($motifs as $motif)
    <li>{{$motif->observation}}</li>

    @endforeach
</ol>
<br>
Cordialement,
    
</body>
</html>