{{--
    Liste des dossiers recevables, à inscrire à une session d'examen.

    Tirage de travail pour la DFEA : il reprend la liste de l'écran, dans le
    même ordre, avec ce qu'il faut pour préparer la séance (localisation, GUPS
    de rattachement, capacité, présence de l'enquête sociale).
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dossiers à inscrire en session</title>

    <style>
        @page { margin: 80px 28px 55px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #1f2937; }

        #footer { position: fixed; bottom: -35px; left: 0; right: 0; font-size: 7.5px; color: #6b7280;
                  border-top: 1px solid #d1d5db; padding-top: 4px; }
        #footer table { width: 100%; border-collapse: collapse; }
        #footer td { border: none; padding: 0; }
        .pagenum:before { content: counter(page); }

        .title-block { text-align: center; margin: 4px 0 12px 0; }
        .title-block h1 { font-size: 13px; margin: 0 0 3px 0; text-transform: uppercase; letter-spacing: .5px; }
        .title-block .meta { font-size: 8.5px; color: #4b5563; }

        table.list { width: 100%; border-collapse: collapse; }
        table.list th { background: #f3f4f6; border: 1px solid #d1d5db; padding: 4px 5px;
                        text-align: left; font-size: 7.5px; text-transform: uppercase; color: #374151; }
        table.list td { border: 1px solid #e5e7eb; padding: 4px 5px; vertical-align: top; }
        table.list td.num { text-align: center; width: 20px; }
        table.list td.center { text-align: center; }
        table.list tr { page-break-inside: avoid; }
        thead { display: table-header-group; }

        .empty { color: #9ca3af; }
        .none { color: #6b7280; font-style: italic; padding: 10px 0; text-align: center; }
        .signature { margin-top: 24px; font-size: 8.5px; color: #4b5563; text-align: right; }
    </style>
</head>
<body>

<div id="footer">
    <table>
        <tr>
            <td>Dossiers à inscrire en session — édité le {{ now()->format('d/m/Y à H:i') }}</td>
            <td style="text-align: right;"><i>Page <span class="pagenum"></span></i></td>
        </tr>
    </table>
</div>

@include('pdf.partials.entete')

<div class="title-block">
    <h1>Dossiers à inscrire en session</h1>
    <p class="meta">
        {{ $service?->name ?? 'Tous services' }} &nbsp;·&nbsp;
        {{ $requetes->count() }} dossier{{ $requetes->count() > 1 ? 's' : '' }} recevable{{ $requetes->count() > 1 ? 's' : '' }},
        non encore inscrit{{ $requetes->count() > 1 ? 's' : '' }} à une session
    </p>
</div>

@if ($requetes->isEmpty())
    <p class="none">Aucun dossier en attente d'inscription à une session.</p>
@else
    <table class="list">
        <thead>
            <tr>
                <th>N°</th>
                <th>Code</th>
                <th>Dénomination</th>
                <th>Type</th>
                <th>Promoteur</th>
                <th>Contact</th>
                <th>Département</th>
                <th>Commune</th>
                <th>Arrondissement</th>
                <th>GUPS</th>
                <th>Capacité</th>
                <th>Déposé le</th>
                <th>Enquête</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requetes as $requete)
                @php $municipality = $requete->district?->Municipality; @endphp
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td>{{ $requete->code }}</td>
                    <td>{{ $requete->name }}</td>
                    <td>{{ $requete->TypeCape?->name ?? '—' }}</td>
                    <td>{{ trim(($requete->name_pomoter ?? '').' '.($requete->firstname_pomoter ?? '')) ?: '—' }}</td>
                    <td>
                        {{ $requete->phone ?? '' }}
                        @if ($requete->email)<br/>{{ $requete->email }}@endif
                    </td>
                    <td>{{ $municipality?->Department?->name ?? '—' }}</td>
                    <td>{{ $municipality?->name ?? $requete->town ?? '—' }}</td>
                    <td>{{ $requete->district?->name ?? '—' }}</td>
                    <td>{{ $requete->district?->cps?->name ?? '—' }}</td>
                    <td class="center">{{ $requete->capacity ?? '—' }}</td>
                    <td class="center">{{ $requete->created_at?->format('d/m/Y') }}</td>
                    <td class="center">{{ $requete->has_cps_file ? 'Oui' : 'Non' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="signature">Fait à Cotonou, le {{ now()->format('d/m/Y') }}</p>
@endif

</body>
</html>
