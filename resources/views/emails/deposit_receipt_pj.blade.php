




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
    <p style="text-align:left" class="mb-3">
            N°_____/MASM/DC/SGM/DGAS/DFEA/SPCA/SA
    </p>
        <h3 class="text-center"><u><b>RECEPISSE DE DEPOT</b></u></h3>
    <p>

   {{ $decret ?? ""}},
    <b>Madame/Monsieur </b>{{$cape->name_pomoter}},<b> promoteur/trice de</b> {{$cape->name}} <b>situé(e)</b> dans l' arrondissement de {{$cape->district->name}}, commune de {{$cape->district->municipality->name}}
    a régulièrement soumis son dossier de demande au ministère en charge des affaires sociales à travers la plate dédiée à ce effet. 
     </p>
     <br><br>
    <p>Après vérification des différentes pièces constitutives au niveau du centre de promotion sociale, le dossier physique est conforme à celui déposé en ligne.  </p>
     <br><br>
     <p>
     En foi de  quoi, le présent récépissé lui est délivré automatiquement par le système pour servir et valoir ce que de droit.

     </p>
     <br><br>



      <div style="text-align:right">
      <div class="">
      <ul  style="margin-bottom: 20px" style="list-style:none">
            <li>Pour le DDASM et PO</li>
            <!-- <li>{{$cape->district->cps->name}}</li> -->
        </ul>
      </div>
       
    <img src="data:image/png;base64, {!! base64_encode(QrCode::size(100)->generate(env('APP_FRONT_URL').'/verify-document/'.$token)) !!} ">



        <!-- <h6><b>{{$cape->district->cps->name}}</b></h6> -->
        <h6><b>Le Chef du Centre de Promotion Sociale</b></h6>
      </div>
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