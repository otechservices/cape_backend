

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFAS | RECEPISSE D’INSCRIPTION</title>
   
    <div id="footer">
    <i> Page <span class="pagenum"></span> </i>
    </div>
    <style>
        hr{page-break-after: always;}
            /* FOOTER */
            #footer {width: 100%;text-align: right;position: fixed;}
            #footer {bottom: -15px;}
            .pagenum:before {content: counter(page);}
        /* ENTETE */
        
        .container{
            margin-left:5rem;
            margin-right:5rem;
            margin-bottom:2rem;
        }
        .right {
            float: right;
            }
            .left {
            float: left;
            }
            .address{
                list-style: none;
            }
            .address li{
                text-align:right
            }

            .header-img{
                height:50px;
            }
            .green{
                background-color:green;
            }
            .yellow{
                background-color:yellow;
            }
            .red{
                background-color:red;
            }
            .drag{
                width: 100px;
                height:10px;
                box-sizing: border-box;
            }
            .drag-content{
                text-align:center
            }
            .drag-content .drag{
                display:inline-block;
            }
            .header {
                height:150px;
                font-size:12px;
                }
            .footer {
                /* margin-left:30%; */
                margin-top:5rem;
                margin-bottom:5rem;
                height:50px;
            }
            .page-break {
                page-break-after: always;
            }
            .text-center{
                text-align:center
            }
            @media print {
            body {
                -webkit-print-color-adjust: exact;
            }
        }
        
        .tb {
            border: 1px solid black;
            text-align:center;
            border-collapse: collapse;
        } 
        .ts {
            border: 1px solid black;
            text-align:left;
            border-collapse: collapse;
        } 
        .cent{
            background-color : #cccccb;
        }
                
        #oa {
            height: 30px;
            width: 30px;
            background: #92d050;
            -ms-transform: rotate(45deg); /* Internet Explorer */
            -moz-transform: rotate(45deg); /* Firefox */
            -webkit-transform: rotate(45deg); /* Safari et Chrome */
            -o-transform: rotate(45deg); /* Opera */
        }
        #cp1 {
            height: 30px;
            width: 30px;
            background: #ffff00;
            -ms-transform: rotate(45deg); /* Internet Explorer */
            -moz-transform: rotate(45deg); /* Firefox */
            -webkit-transform: rotate(45deg); /* Safari et Chrome */
            -o-transform: rotate(45deg); /* Opera */
        }
        #cp2 {
            height: 30px;
            width: 30px;
            background: #ffc000;
            -ms-transform: rotate(45deg); /* Internet Explorer */
            -moz-transform: rotate(45deg); /* Firefox */
            -webkit-transform: rotate(45deg); /* Safari et Chrome */
            -o-transform: rotate(45deg); /* Opera */
        }
        #cp3 {
            height: 30px;
            width: 30px;
            background: #ff0000;
            -ms-transform: rotate(45deg); /* Internet Explorer */
            -moz-transform: rotate(45deg); /* Firefox */
            -webkit-transform: rotate(45deg); /* Safari et Chrome */
            -o-transform: rotate(45deg); /* Opera */
        }
    .legendepl{
        text-align:left;
    }
    .legendedir{
        text-align:left; 
        padding-left:30;
    }
    .cache_balise{
        display:none;
    }

    .vu{
        margin-right:10px;
    }
    </style>
</head>
<body>
    @include('pdf.partials.entete')
    <div class="delimiter">
        <h3 class="text-center"><u><b>ARRÊTÉ</b></u></h3>
        <p style="text-align:center ">ANNÉE 2022 N°____/MASM/DC/SGM/DGAS/DFEA/SPECA/SA/SGG22</p>
        <p style="text-align:center ">Portant agrément du Centre d’Accueil et de Protection d’Enfants dénommé {{$name??"Nom CAPE"}}</p>
        <p style="text-align:center ">LE MINISTRE DES AFFAIRES SOCIALES ET DE LA MICROFINANCE,</p>

        <p>
            <ul style="list-style:none">
                <li> <span class="vu">Vu</span>la loi N° 90-32 du 11 décembre 1990 portant Constitution de la République du Bénin, telle que modifiée par la loi n° 2019-40 du 07 novembre 2019 ;</li>
                <li> <span class="vu">Vu</span>la loi 2015-08 du 08 décembre 2015 portant Code de l’Enfant en République du Bénin ;</li>
                <li> <span class="vu">Vu</span>la proclamation, par la Cour constitutionnelle, des résultats définitifs de l’élection présidentielle en date du 11 avril 2021;</li>
                <li> <span class="vu">Vu</span>le décret n° 2021-257 du 25 mai 2021 portant composition du Gouvernement ;</li>
                <li> <span class="vu">Vu</span>le décret n°2021-401 du 28 juillet 2021 fixant la structure-type des Ministères modifié par le décret n° 2022-476 du 03 aout 2022 portant modification des articles 58, 64 et 82 ;</li>
                <li> <span class="vu">Vu</span>le décret n° 3743/MFASSNHPTA/DC/SGM/DFEA/SA du 16 septembre 2014 portant attributions, organisation et fonctionnement du Comité national d’étude de dossiers de demande d’autorisation d’ouverture de Centre d’Accueil et de Protection d’Enfants (CAPE) ;</li>
                <li> <span class="vu">Vu</span>le décret n° 0194/MFASSNHPTA/DC/SGM/DRFM/DFEA/SA du 19 janvier 2015 fixant les frais d’étude des dossiers de demande d’autorisation d’ouverture de Centres d’Accueil et de Protection d’Enfants (CAPE) ;</li>
                <li> <span class="vu">Vu</span>le décret n° 2022-072 du 09 février 2022 fixant les modalités de création, d’organisation et de fonctionnement des centres d’accueil et de protection de l’enfant en République du Bénin ;</li>
                <li> <span class="vu">Vu</span>le décret n° 2022-606 du 02 novembre 2022 portant attributions, organisation et fonctionnement du Ministère des Affaires Sociales et de la Microfinance ;</li>
  
            </ul>
        </p>
        <p>
        Considérant le procès-verbal des sessions d’étude des dossiers de demande d’autorisation d’ouverture de CAPE tenues les 16, 17 et 18 août 2022 ; 
        Considérant le procès-verbal des délibérations faites par le Comité national d’étude des dossiers de demande d’autorisation d’ouverture de CAPE les 25, 26 et 27 octobre et les 10 et 11 novembre 2022, à Cotonou 

        </p>

        <h3 class="text-center">ARRÊTÉ:</h3>
        <h5>Article premier</h5>
        <p>L’Orphelinat {{$name??"Nom CAPE"}}, située dans la Commune de Porto-Novo, 2ème arrondissement, quartier Djègan-Daho tél (+229) 97972301, est agréé aux fins d’accueillir et d’assurer la prise en charge des enfants orphelins de la naissance à leur 17ème anniversaire conformément aux dispositions de l’article 3 du décret n°2022-072 du 09 février 2022 fixant les modalités de création, d’organisation et de fonctionnement des centres d’accueil et de protection de l’enfant en République du Bénin.</p>

        <h5>Article 2  </h5>

        <p>Monsieur SEMEI Mohamed est le promoteur et directeur de l’Orphelinat {{$name??"Nom CAPE"}}. </p>

        <h5>Article 3  </h5>

            <p>L’Orphelinat {{$name??"Nom CAPE"}} exerce ses activités sous la supervision du Centre de Promotion Sociale (CPS) de Agbokou et lui transmet ses rapports trimestriels d’activités. </p>

        <h5>Article 4  </h5>

        <p>L’Orphelinat {{$name??"Nom CAPE"}} est tenu de se conformer aux dispositions des articles 16 à 26,  31 et 32 du décret n° 2022-072 du 09 février 2022 fixant les modalités de création, d’organisation et de fonctionnement des Centres d’Accueil et de Protection d’Enfants (CAPE) en République du Bénin.</p>

        <h5>Article 5  </h5>

        <p>Dans le cadre de ses activités, l’Orphelinat {{$name??"Nom CAPE"}} prend toutes les dispositions nécessaires pour garantir aux enfants du centre :</p>
        <ul style="list-style:none">
            <li><span>- </span>des vêtements personnels décents, propres, adaptés à la saison et à l’activité menée ;</li>
            <li><span>- </span>un logement spacieux soustrait à la promiscuité, et un environnement sain ;</li>
            <li><span>- </span>une aire/une salle de jeux ou d’activités socioculturelles et d’éveil individuel et/ou collectif </li>
            <li><span>- </span>leurs droits à la participation, à l’information et à l’écoute</li>
        </ul>
     
        <h5>Article 6  </h5>
     <p>
     Le CAPE {{$name??"Nom CAPE"}} peut faire l’objet des sanctions prévues à l’article 69 du décret N°2022-072 du 09 février 2022 fixant les modalités de création, d’organisation et de fonctionnement des centres d’accueil et de protection de l’enfant en République du Bénin. Lesdites sanctions sont progressives allant de rappel à l'ordre au retrait d'autorisation.
     </p>
     <h5>Article 7  </h5>
    <p>Les sanctions sont consécutives à des rapports motivés produits par les structures de protection de l’enfant étatiques. Elles sont notifiées au promoteur par courrier administratif signé par les autorités. </p>
    <p>La fermeture est notifiée par le Ministre en charge des Affaires Sociales. Sa mise en application requiert que les dispositions soient prises pour la protection des pensionnaires. 	</p>

    <h5>Article 8</h5>

    <p>Le présent arrêté prend effet pour compter de la date de sa signature. Il sera publié au Journal officiel.</p>


     <br><br>

      <p style="margin-left:60vw">
        Cotonou, le {{date('d/m/Y')}}
        </p>
<br><br>
<p style="margin-left:60vw">
Véronique TOGNIFODE     
        </p>

    </div>
  
    <footer class="footer">
        <div class="drag-content">
            <div class="green drag"></div>
            <div class="yellow drag"></div>
            <div class="red drag"></div>
        </div>
    </footer>

    <p>
        <small>
        <span><u>AMPLIATIONS:</u></span> Original 01 ; PR 01 ; An 01 ; CC 01 ; CS 01 ; HAAC 01 ; CES 01 ; DAF 01 ; SGM 01 ; DC 01 ; Autres Ministères 21 ; Directions Générales MASM 02 ; Directions Techniques DGAS 04 ; DDASM 12 ; CAPE 37 ; Archives 01 ; JO 01.
        </small>
    </p>
</body>
</html>