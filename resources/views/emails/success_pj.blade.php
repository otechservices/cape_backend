

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MASM | RECEPISSE D’INSCRIPTION</title>
   
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
    <table style="width:100%; margin-top:-10px; margin-left:-20px; margin-right:-20px; ">
        <tr>
            <td style="width:40%;">
                <img src="data:image/png;base64,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" class="header-img" alt=""/>
            </td>
            <td style="width:30%; text-align:center;">
            </td>
            <td style="width:30%;">
                <ul class="address">
                    <li>01 BP 907 Cotonou</li>
                    <li>BENIN</li>
                    <li>TEL: + 229 60 42 02 02</li>
                    <li> masm.dfea@gouv.bj</li>
                    <li>www.social.gouv.bj</li>
                </ul>
            </td>
        </tr>
	</table>
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