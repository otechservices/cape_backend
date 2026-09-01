

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
    </style>
</head>
<body>
    @include('pdf.partials.entete')
    <div class="delimiter">
        <h3 class="text-center"><u><b>RECEPISSE D’INSCRIPTION</b></u></h3>
        <p>
            
        </p>
     <p>
        Le Centre d’accueil et de protection de l’enfant dénommé {{$name ?? "Nom CAPE"}}, 
        <br>
        localisé comme suit : 
        <ul>
            <li>Département : {{$cape?->district->municipality?->department?->name}}</li>
            <li>Commune : {{$cape?->district->municipality?->name}}</li>
            <li>Arrondissement  : {{$cape?->district?->name}}</li>
            <li>Quartier/Village  : {{$cape?->town}}</li>
            <li>Adresse précise : {{$cape?->address}}</li>
            <li>Dont le promoteur est  : {{$cape?->promoter?->lastname}} {{$cape?->promoter?->firstname}}</li>

        </ul>

        S’est inscrit sur la plateforme numérique en ligne de gestion des CAPE le {{date_format(date_create($cape?->created_at),"d-m-Y")}} à {{date_format(date_create($cape?->created_at),"h:i:s")}} d’inscription » et sera invité à déposer son dossier auprès du Centre de Promotion Sociale de « {{$cape?->district?->name}} » conformément aux dispositions de l’article 14 du décret n°2022-072 du 09 février 2022 fixant les modalités de création, d’organisation et de fonctionnement des centres d’accueil et de protection de l’enfant en République du Bénin.

     <br><br>
     </p>

     <p>
     En foi de quoi, le présent récépissé lui est délivré automatiquement par le système pour servir et valoir ce que de droit. 

     </p>
     <br><br>

      <p class="text-center">
      Signature de l’Autorité compétente
      </p>
    </div>
  
    <footer class="footer">
        <div class="drag-content">
            <div class="green drag"></div>
            <div class="yellow drag"></div>
            <div class="red drag"></div>
        </div>
    </footer>
</body>
</html>