@php
    /** Valeur affichable, ou un tiret si l'information n'a jamais été saisie. */
    $v = fn ($value) => filled($value) ? e($value) : '<span class="empty">—</span>';
    $d = fn ($value) => filled($value) ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '<span class="empty">—</span>';
    $oui = fn ($value) => $value ? 'Oui' : 'Non';

    $estGarderie = str_contains(strtolower((string) $requete->service?->name), 'garderie');
    $estAgree = $requete->is_agree;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche d'état — {{ $requete->name }}</title>

    <style>
        @page { margin: 90px 40px 70px 40px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #1f2937; }

        /* Deux colonnes via un tableau, sans float : un flottant dans un bloc
           position:fixed fait doubler la pagination de dompdf. */
        #footer { position: fixed; bottom: -45px; left: 0; right: 0; font-size: 8px; color: #6b7280;
                  border-top: 1px solid #d1d5db; padding-top: 4px; }
        #footer table { width: 100%; border-collapse: collapse; }
        #footer td { border: none; padding: 0; }
        .pagenum:before { content: counter(page); }

        .title-block { text-align: center; margin: 4px 0 14px 0; }
        .title-block h1 { font-size: 14px; margin: 0 0 3px 0; text-transform: uppercase; letter-spacing: .5px; }
        .title-block .subject { font-size: 11.5px; font-weight: bold; margin: 0; }
        .title-block .meta { font-size: 8.5px; color: #4b5563; margin-top: 3px; }

        .badge { padding: 2px 8px; border-radius: 9px; font-size: 8px;
                 font-weight: bold; border: 1px solid #9ca3af; color: #374151; }
        .badge-agree { border-color: #15803d; color: #15803d; }

        h2.section { font-size: 10px; text-transform: uppercase; letter-spacing: .4px;
                     background: #eef2f7; border-left: 3px solid #1e3a8a; color: #1e3a8a;
                     padding: 4px 7px; margin: 14px 0 6px 0; page-break-after: avoid; }

        table.grid { width: 100%; border-collapse: collapse; }
        table.grid td { padding: 3px 6px; vertical-align: top; border-bottom: 1px solid #e5e7eb; }
        table.grid td.label { width: 24%; color: #6b7280; font-size: 8.5px; text-transform: uppercase; }
        table.grid td.value { width: 26%; font-weight: bold; }

        table.list { width: 100%; border-collapse: collapse; margin-top: 2px; }
        table.list th { background: #f3f4f6; border: 1px solid #d1d5db; padding: 4px 6px;
                        text-align: left; font-size: 8.5px; text-transform: uppercase; color: #374151; }
        table.list td { border: 1px solid #e5e7eb; padding: 4px 6px; vertical-align: top; }
        table.list td.num { text-align: center; width: 22px; }
        table.list tr { page-break-inside: avoid; }

        .empty { color: #9ca3af; font-weight: normal; }
        .none { color: #6b7280; font-style: italic; padding: 5px 0; }
        table.stats { width: 100%; border-collapse: collapse; }
        table.stats td { text-align: center; border: 1px solid #d1d5db; padding: 6px; width: 25%; }
        table.stats td.n { font-size: 15px; font-weight: bold; color: #1e3a8a;
                           border-bottom: none; padding-bottom: 0; }
        table.stats td.k { font-size: 8px; text-transform: uppercase; color: #6b7280;
                           border-top: none; padding-top: 2px; }
        .signature { margin-top: 26px; font-size: 8.5px; color: #4b5563; }
        .flag { margin-top: 12px; text-align: center; }
        .flag span { display: inline-block; width: 70px; height: 8px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

<div id="footer">
    <table>
        <tr>
            <td>Fiche d'état — {{ $requete->code }}</td>
            <td style="text-align: right;"><i>Page <span class="pagenum"></span></i></td>
        </tr>
    </table>
</div>

@include('pdf.partials.entete')

<div class="title-block">
    <h1>Fiche d'état {{ $estGarderie ? "d'une garderie" : "d'un centre d'accueil et de protection de l'enfance" }}</h1>
    <p class="subject">{{ $requete->name }}</p>
    <p class="meta">
        Dossier n° {{ $requete->code }} &nbsp;·&nbsp;
        {{ $requete->service?->name }} &nbsp;·&nbsp;
        <span class="badge {{ $estAgree ? 'badge-agree' : '' }}">{{ $estAgree ? 'Agréé' : 'Non agréé' }}</span>
        &nbsp;·&nbsp; {{ \App\Exports\RequetesExport::statusLabel($requete->status) }}
    </p>
</div>

<h2 class="section">1. Identification du centre</h2>
<table class="grid">
    <tr>
        <td class="label">Dénomination</td><td class="value">{!! $v($requete->name) !!}</td>
        <td class="label">Code dossier</td><td class="value">{!! $v($requete->code) !!}</td>
    </tr>
    <tr>
        <td class="label">Nature</td><td class="value">{!! $v($requete->service?->name) !!}</td>
        <td class="label">{{ $estGarderie ? 'Type de garderie' : 'Type de centre' }}</td>
        <td class="value">
            @if($estGarderie && $requete->RequeteTypeGarderies->isNotEmpty())
                {{ $requete->RequeteTypeGarderies->map(fn ($g) => $g->TypeGarderie?->name)->filter()->implode(', ') }}
            @else
                {!! $v($requete->TypeCape?->name) !!}
            @endif
        </td>
    </tr>
    <tr>
        <td class="label">Capacité d'accueil</td><td class="value">{!! $v($requete->capacity) !!}</td>
        <td class="label">Objet / vocation</td><td class="value">{!! $v($requete->purpose) !!}</td>
    </tr>
    <tr>
        <td class="label">Email du centre</td><td class="value">{!! $v($requete->email) !!}</td>
        <td class="label">Téléphone du centre</td><td class="value">{!! $v($requete->phone) !!}</td>
    </tr>
    <tr>
        <td class="label">Responsable / directeur</td>
        <td class="value">{!! $v(trim(($requete->name_chief ?? '').' '.($requete->firstname_chief ?? ''))) !!}</td>
        <td class="label">Contact du responsable</td>
        <td class="value">{!! $v($requete->phone_chief) !!} @if(filled($requete->email_chief)) <br>{{ $requete->email_chief }} @endif</td>
    </tr>
    <tr>
        <td class="label">Cibles accueillies</td>
        <td class="value" colspan="3">
            @if(count($targets)) {{ implode(' · ', $targets) }} @else <span class="empty">—</span> @endif
        </td>
    </tr>
</table>

<h2 class="section">2. Localisation</h2>
<table class="grid">
    <tr>
        <td class="label">Département</td><td class="value">{!! $v($requete->district?->Municipality?->Department?->name) !!}</td>
        <td class="label">Commune</td><td class="value">{!! $v($requete->district?->Municipality?->name ?? $requete->town) !!}</td>
    </tr>
    <tr>
        <td class="label">Arrondissement</td><td class="value">{!! $v($requete->district?->name) !!}</td>
        <td class="label">Quartier / village</td><td class="value">{!! $v($requete->town) !!}</td>
    </tr>
    <tr>
        <td class="label">Adresse</td><td class="value">{!! $v($requete->address) !!}</td>
        <td class="label">Coordonnées GPS</td><td class="value">{!! $v($requete->coords) !!}</td>
    </tr>
    <tr>
        <td class="label">CPS de rattachement</td><td class="value">{!! $v($requete->district?->cps?->name) !!}</td>
        <td class="label">Chef CPS</td><td class="value">{!! $v($requete->district?->cps?->name_chief) !!}</td>
    </tr>
</table>

<h2 class="section">3. Promoteur</h2>
<table class="grid">
    <tr>
        <td class="label">Nature du promoteur</td><td class="value">{!! $v($requete->NaturePromotor?->name) !!}</td>
        <td class="label">Compte promoteur</td>
        <td class="value">
            {!! $v(trim(($requete->promoter?->lastname ?? '').' '.($requete->promoter?->firstname ?? ''))) !!}
        </td>
    </tr>
    <tr>
        <td class="label">Nom et prénom(s)</td>
        <td class="value">{!! $v(trim(($requete->name_pomoter ?? '').' '.($requete->firstname_pomoter ?? ''))) !!}</td>
        <td class="label">Contacts</td>
        <td class="value">{!! $v($requete->phone_pomoter) !!} @if(filled($requete->email_pomoter)) <br>{{ $requete->email_pomoter }} @endif</td>
    </tr>
    <tr>
        <td class="label">Promoteur directeur</td><td class="value">{{ $oui($requete->pomoter_is_director) }}</td>
        <td class="label">Consentement fourni</td><td class="value">{{ $oui($requete->has_consent) }}</td>
    </tr>
</table>

@if(str_contains(strtolower((string) $requete->NaturePromotor?->name), 'morale'))
    <table class="grid">
        <tr>
            <td class="label">Dénomination sociale</td><td class="value">{!! $v($requete->social_reason) !!}</td>
            <td class="label">Siège</td><td class="value">{!! $v($requete->head_office) !!}</td>
        </tr>
        <tr>
            <td class="label">N° d'enregistrement</td><td class="value">{!! $v($requete->registered_number) !!}</td>
            <td class="label">Date d'enregistrement</td><td class="value">{!! $d($requete->registered_date) !!}</td>
        </tr>
        <tr>
            <td class="label">Contact structure</td><td class="value" colspan="3">{!! $v($requete->registered_phone) !!}</td>
        </tr>
    </table>
@endif

<h2 class="section">4. Situation administrative</h2>
<table class="grid">
    <tr>
        <td class="label">Statut du dossier</td>
        <td class="value">{{ \App\Exports\RequetesExport::statusLabel($requete->status) }}</td>
        <td class="label">Date de soumission</td><td class="value">{!! $d($requete->created_at) !!}</td>
    </tr>
    <tr>
        <td class="label">Agrément</td><td class="value">{{ $estAgree ? 'Agréé' : 'Non agréé' }}</td>
        <td class="label">Référence de l'agrément</td><td class="value">{!! $v($requete->aggreement_reference) !!}</td>
    </tr>
    <tr>
        <td class="label">Année de l'agrément</td><td class="value">{!! $v($requete->aggreement_year) !!}</td>
        <td class="label">Date d'inscription au registre</td><td class="value">{!! $d($cape?->created_at) !!}</td>
    </tr>
    <tr>
        <td class="label">Session d'examen</td><td class="value">{!! $v($requete->session?->name) !!}</td>
        <td class="label">Note de session</td><td class="value">{!! $v($requete->note_session) !!}</td>
    </tr>
    <tr>
        <td class="label">Note terrain</td><td class="value">{!! $v($requete->note_terrain) !!}</td>
        <td class="label">Note finale</td><td class="value">{!! $v($requete->note_finale) !!}</td>
    </tr>
    <tr>
        <td class="label">Observation finale</td>
        <td class="value" colspan="3">{!! $v($requete->final_observation ?? $requete->observation) !!}</td>
    </tr>
</table>

<h2 class="section">5. Enquête sociale</h2>
@if($requete->has_cps_file)
    <table class="grid">
        <tr>
            <td class="label">Enquêteur</td><td class="value">{!! $v($requete->social_investigator_name) !!}</td>
            <td class="label">Date de l'enquête</td><td class="value">{!! $d($requete->social_investigator_date) !!}</td>
        </tr>
        <tr>
            <td class="label">Observation</td>
            <td class="value" colspan="3">{!! $v($requete->social_investigator_observation) !!}</td>
        </tr>
    </table>
@else
    <p class="none">Aucune enquête sociale enregistrée pour ce centre.</p>
@endif

<h2 class="section">6. Dépôt physique du dossier</h2>
@if($requete->has_physical_deposit)
    <table class="grid">
        <tr>
            <td class="label">Déposant</td><td class="value">{!! $v($requete->name_depositor) !!}</td>
            <td class="label">Contact</td><td class="value">{!! $v($requete->phone_depositor) !!}</td>
        </tr>
        <tr>
            <td class="label">Date du dépôt</td><td class="value">{!! $d($requete->date_depositor) !!}</td>
            <td class="label">Observation</td><td class="value">{!! $v($requete->observation_depositor) !!}</td>
        </tr>
    </table>
@else
    <p class="none">Aucun dépôt physique enregistré.</p>
@endif

<h2 class="section">7. Pièces constitutives du dossier</h2>
@if($requete->files->isNotEmpty())
    <table class="list">
        <thead>
            <tr>
                <th class="num">#</th>
                <th>Pièce</th>
                <th style="width:110px;">Conformité</th>
                <th style="width:33%;">Observation</th>
            </tr>
        </thead>
        <tbody>
        @foreach($requete->files as $f)
            <tr>
                <td class="num">{{ $loop->iteration }}</td>
                <td>{{ $f->file?->name ?? $f->reference }}</td>
                <td>
                    @if(! $f->is_treated) Non traitée
                    @elseif($f->is_valid) Conforme
                    @else Non conforme
                    @endif
                </td>
                <td>{!! $v($f->observation) !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p class="none">Aucune pièce jointe au dossier.</p>
@endif

<h2 class="section">8. Documents délivrés par l'administration</h2>
@if($requete->files2->isNotEmpty())
    <table class="list">
        <thead>
            <tr><th class="num">#</th><th>Référence</th><th style="width:120px;">Date</th></tr>
        </thead>
        <tbody>
        @foreach($requete->files2 as $f)
            <tr>
                <td class="num">{{ $loop->iteration }}</td>
                <td>{{ $f->reference }}</td>
                <td>{!! $d($f->created_at) !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p class="none">Aucun document délivré à ce jour.</p>
@endif

<div class="page-break"></div>

<h2 class="section">9. Exploitation du centre</h2>
<table class="stats">
    <tr>
        <td class="n">{{ $residents_actifs }}</td>
        <td class="n">{{ $residents_abandons }}</td>
        <td class="n">{{ $staffs->count() }}</td>
        <td class="n">{{ $activity_reports->count() }}</td>
    </tr>
    <tr>
        <td class="k">Pensionnaires présents</td>
        <td class="k">Enfants abandonnés</td>
        <td class="k">Membres du personnel</td>
        <td class="k">Rapports d'activité</td>
    </tr>
</table>

<h2 class="section">9.1 Personnel</h2>
@if($staffs->isNotEmpty())
    <table class="list">
        <thead>
            <tr><th class="num">#</th><th>Nom et prénom(s)</th><th>Fonction</th><th>Contact</th><th>Email</th></tr>
        </thead>
        <tbody>
        @foreach($staffs as $s)
            <tr>
                <td class="num">{{ $loop->iteration }}</td>
                <td>{{ trim($s->lastname.' '.$s->firstname) }}</td>
                <td>{!! $v($s->job) !!}</td>
                <td>{!! $v($s->phone) !!}</td>
                <td>{!! $v($s->email) !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p class="none">Aucun membre du personnel déclaré.</p>
@endif

<h2 class="section">9.2 Pensionnaires</h2>
@if($residents->isNotEmpty())
    <table class="list">
        <thead>
            <tr>
                <th class="num">#</th><th>Nom et prénom(s)</th><th style="width:40px;">Sexe</th>
                <th style="width:80px;">Naissance</th><th>Lieu de naissance</th><th style="width:80px;">Situation</th>
            </tr>
        </thead>
        <tbody>
        @foreach($residents as $r)
            <tr>
                <td class="num">{{ $loop->iteration }}</td>
                <td>{{ trim($r->lastname.' '.$r->firstname) }}</td>
                <td>{!! $v($r->sex) !!}</td>
                <td>{!! $d($r->birthdate) !!}</td>
                <td>{!! $v($r->birthplace) !!}</td>
                <td>{{ $r->abandon ? 'Abandonné' : 'Présent' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p class="none">Aucun pensionnaire enregistré.</p>
@endif

<h2 class="section">9.3 Rapports d'activité</h2>
@if($activity_reports->isNotEmpty())
    <table class="list">
        <thead>
            <tr><th class="num">#</th><th style="width:90px;">Déposé le</th><th>Description</th><th style="width:110px;">Statut</th></tr>
        </thead>
        <tbody>
        @foreach($activity_reports as $a)
            <tr>
                <td class="num">{{ $loop->iteration }}</td>
                <td>{!! $d($a->created_at) !!}</td>
                <td>{!! $v($a->description) !!}</td>
                <td>
                    @switch($a->status)
                        @case(1) Validé @break
                        @case(2) Rejeté @break
                        @case(3) Corrigé @break
                        @default Nouveau
                    @endswitch
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p class="none">Aucun rapport d'activité déposé.</p>
@endif

<h2 class="section">10. Contrôles effectués</h2>
@if($controls->isNotEmpty())
    <table class="list">
        <thead>
            <tr><th class="num">#</th><th style="width:80px;">Date</th><th style="width:22%;">Type</th>
                <th>Observation</th><th style="width:70px;">Validé</th></tr>
        </thead>
        <tbody>
        @foreach($controls as $c)
            <tr>
                <td class="num">{{ $loop->iteration }}</td>
                <td>{!! $d($c->date_control) !!}</td>
                <td>{!! $v($c->TypeControl?->name) !!}</td>
                <td>{!! $v($c->observation) !!}</td>
                <td>{{ $oui($c->is_valid) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p class="none">Aucun contrôle enregistré.</p>
@endif

<h2 class="section">11. Sanctions</h2>
@if($sanctions->isNotEmpty())
    <table class="list">
        <thead>
            <tr><th class="num">#</th><th style="width:22%;">Type</th><th>Description</th>
                <th style="width:80px;">Prononcée le</th><th style="width:80px;">Levée le</th></tr>
        </thead>
        <tbody>
        @foreach($sanctions as $s)
            <tr>
                <td class="num">{{ $loop->iteration }}</td>
                <td>{!! $v($s->TypeSanction?->name) !!}</td>
                <td>{!! $v($s->description) !!}</td>
                <td>{!! $d($s->created_at) !!}</td>
                <td>{!! $d($s->closed_date) !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p class="none">Aucune sanction prononcée à l'encontre de ce centre.</p>
@endif

<h2 class="section">12. Recommandations</h2>
@if($requete->referals->isNotEmpty())
    <table class="list">
        <thead>
            <tr><th class="num">#</th><th>Recommandation</th><th style="width:90px;">Levée</th></tr>
        </thead>
        <tbody>
        @foreach($requete->referals as $r)
            <tr>
                <td class="num">{{ $loop->iteration }}</td>
                <td>{!! $v($r->libelle) !!}</td>
                <td>{{ $oui($r->is_reached) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p class="none">Aucune recommandation formulée.</p>
@endif

<h2 class="section">13. Décisions des intervenants</h2>
@if($requete->reponses->isNotEmpty())
    <table class="list">
        <thead>
            <tr><th class="num">#</th><th style="width:90px;">Date</th><th style="width:22%;">Intervenant</th>
                <th style="width:110px;">Décision</th><th>Observation</th></tr>
        </thead>
        <tbody>
        @foreach($requete->reponses as $r)
            <tr>
                <td class="num">{{ $loop->iteration }}</td>
                <td>{!! $d($r->created_at) !!}</td>
                <td>{!! $v($r->user?->name) !!}</td>
                <td>
                    @if($r->hasPermission === null) Mise en attente
                    @elseif($r->hasPermission) Dossier validé
                    @else Dossier rejeté
                    @endif
                </td>
                <td>{!! $v(strip_tags((string) $r->observation)) !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p class="none">Aucune décision enregistrée.</p>
@endif

<h2 class="section">14. Historique du dossier</h2>
@if($requete->parcours->isNotEmpty())
    <table class="list">
        <thead>
            <tr><th class="num">#</th><th style="width:100px;">Date</th><th>Étape</th><th style="width:22%;">Agent</th></tr>
        </thead>
        <tbody>
        @foreach($requete->parcours as $p)
            <tr>
                <td class="num">{{ $loop->iteration }}</td>
                <td>{{ $p->created_at?->format('d/m/Y H:i') }}</td>
                <td>{{ $p->libelle }}</td>
                <td>{!! $v($p->user?->name) !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p class="none">Aucun mouvement enregistré sur ce dossier.</p>
@endif

<p class="signature">
    Fiche établie le {{ $generated_at->format('d/m/Y à H:i') }}
    @if($generated_by) par {{ $generated_by->name }} @endif
    — Direction de la Famille, de l'Enfance et de l'Adolescence.<br>
    Document généré automatiquement à partir des données de la plateforme ; il reflète l'état du centre à cette date.
</p>

<div class="flag">
    <span style="background:#008852;"></span><span style="background:#FCD111;"></span><span style="background:#E82231;"></span>
</div>

</body>
</html>
