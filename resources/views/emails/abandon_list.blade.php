




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFAS | LISTE DES ENFANTS ABANDONNES</title>
   
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

    .content    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
     .content th,  .content  td {
        padding: 6px 8px;
        border: 1px solid #ccc;
        text-align: left;
    }
    .content  thead {
        background: #f5f5f5;
        font-weight: bold;
    }
    .content  .text-center {
        text-align: center;
    }
   .content   .text-muted {
        color: #777;
    }
   .content   .clickable {
        color: #0a58ca;
        cursor: pointer;
        text-decoration: underline;
    }
    </style>
</head>
<body>
    @include('pdf.partials.entete')
    
        <h2 style="text-align:center">Liste des enfants abandonnés</h2>


        <div class="content">
                  <table>
    <thead>
        <tr>
            <th style="width: 40px;">#</th>
            <th>Identité</th>
            <th>Date de naissance</th>
            <th>Lieu de naissance</th>
            <th>Taille / poids</th>
            <th>Centre</th>
            <th>Promoteur</th>

        </tr>
    </thead>

    <tbody>
        @foreach ($residents as $d)
            <tr>
                <td>
                    <input
                        type="radio"
                        name="selected"
                        id="radio{{ $d->id }}"
                    >
                </td>

                <td>
                    {{ $d->firstname }} {{ $d->lastname }}
                </td>

                <td class="text-muted">
                    {{ \Carbon\Carbon::parse($d->birthdate)->format('d/m/Y') }}
                </td>

                <td class="text-muted">
                    {{ $d->birthplace }}
                </td>

                <td class="text-muted">
                    {{ $d->size }} / {{ $d->weight }}
                </td>

               <td class="text-muted">
                    {{ $d->centre?->name}}
                </td>
                <td class="text-muted">
                    {{ $d->promoter?->lastname }} {{ $d->promoter?->firstname }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
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