<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href={{ asset("css/style.css") }}>
    <title>Plateforme de Gestion des Structures de Protection de l'Enfant</title>
    <style>
        .main{
            padding: 15px 5px;
            margin: 15px 5px;
        }
        p{
            text-align:justify;
        }
        footer{
            width: 100%;
        }
        footer p{
            display: inline-block;
            height:10px;
            margin-left:5px;
            margin-right:5px;
            font-size:10px;
        }
    </style>
</head>


<body>
    <header class="text-center">
        <img src= {{ asset("Images/logo-masm-2.png")}}   alt="">
    </header>
   <section class="main">
    @yield('body-content')
   </section>
   <footer class="text-center">
    <p> masm.dfea@gouv.bj</p>
    <p>République du Bénin</p>
    <br>
    <p>+229 0121321943</p>
   <p > <a class="fct-text-bleu-azur" target="_blank" href="https://cape.social.gouv.bj">Plateforme de Gestion des Structures de Protection de l'Enfant</a></p>
  </footer>
</body>
</html>
