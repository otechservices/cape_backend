#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Convertit la liste officielle des CAPE agréés (.docx) en CSV d'import.

Le fichier produit alimente `php artisan import:agrees` (voir
app/Console/Commands/ImportAgrees.php).

    python3 scripts/docx_to_capes_csv.py "Liste des CAPE agréés actualise Août 26.docx"

Le document source est un tableau à six colonnes (N°, Dénomination, Commune/
localisation, Département, GUPS, Contacts), entrecoupé de lignes de section
« Département = N ». Il ne couvre que les CAPE : les lignes « Garderie » déjà
présentes dans le CSV sont conservées et renumérotées à la suite.

Choix de traitement, volontairement conservateurs :
  - une cellule GUPS vide est traitée comme une omission, pas une radiation :
    la valeur de l'ancien CSV est reprise si elle existe ;
  - les GUPS sont nettoyés (ponctuation résiduelle, « Parakou I » → « Parakou 1 »)
    car TextMatcher départagerait mal « Parakou I » entre les deux CPS ;
  - les numéros de téléphone sont recollés lorsque le document les coupe
    (« 01 95349493 »), sans jamais fusionner deux numéros distincts.

Dépendances : bibliothèque standard uniquement.
"""

import argparse
import csv
import os
import re
import sys
import zipfile
from xml.etree import ElementTree as ET

NS = {'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'}

FIELDS = [
    'numero', 'denomination', 'service', 'type_cape', 'departement', 'commune',
    'arrondissement', 'localisation', 'gups', 'telephone1', 'telephone2',
    'email', 'nom_promoteur', 'reference_agrement', 'annee_agrement',
]

ROLES = r'(?:Directeur|Directrice|Dir\.?|Responsable|Promoteur|Promotrice|Gérant|Coordonnateur|Président[e]?)'
ROMAN = {'parakou i': 'Parakou 1', 'parakou ii': 'Parakou 2'}


def cell_text(cell):
    parts = []
    for p in cell.findall('.//w:p', NS):
        parts.append(''.join(n.text or '' for n in p.findall('.//w:t', NS)).strip())
    text = ' '.join(x for x in parts if x)
    return re.sub(r'\s+', ' ', text.replace('\xa0', ' ')).strip()


def read_docx(path):
    """Lit le tableau du document et renvoie une ligne par CAPE."""
    root = ET.fromstring(zipfile.ZipFile(path).read('word/document.xml'))
    tables = root.findall('.//w:tbl', NS)
    if not tables:
        raise SystemExit(f'Aucun tableau trouvé dans {path}')

    rows, dept, seen_header = [], None, False
    for tr in tables[0].findall('./w:tr', NS):
        cells = [cell_text(c) for c in tr.findall('./w:tc', NS)]
        joined = ' '.join(cells).strip()

        if not seen_header and cells and cells[0].startswith('N'):
            seen_header = True
            continue

        section = re.match(r"^\s*([A-Za-zÀ-ÿ' -]+)\s*=\s*\d+\s*$", joined)
        if section:
            dept = section.group(1).strip()
            continue

        if len(cells) < 5 or not cells[0].strip().isdigit():
            continue

        rows.append({
            'numero': int(cells[0]),
            'denomination': cells[1],
            'localisation': cells[2],
            'departement': cells[3] or dept,
            'gups': cells[4],
            'contacts': cells[5] if len(cells) > 5 else '',
        })
    return rows


def phones(contact):
    """Numéros d'au moins 8 chiffres, en recollant les groupes trop courts."""
    out, buf = [], ''
    for token in re.findall(r'\d+', contact or ''):
        if buf and len(buf) < 8:
            buf += token
        else:
            if buf:
                out.append(buf)
            buf = token
    if buf:
        out.append(buf)

    unique = []
    for n in out:
        if len(n) >= 8 and n not in unique:
            unique.append(n)
    return unique


def promoter(contact):
    """Nom de personne cité dans les contacts, s'il est identifiable."""
    if not contact:
        return ''
    txt = re.sub(r'\d[\d\s]*', ' ', contact)
    txt = re.sub(r'[()«»,;:/]+', ' ', txt)
    txt = re.sub(r'\b' + ROLES + r'\b', ' ', txt, flags=re.I)
    txt = re.sub(r'\s+', ' ', txt).strip(' .-')

    words = [w for w in txt.split() if len(w) > 1]
    if len(txt) < 4 or not words:
        return ''
    if not any(w.isupper() and len(w) > 2 for w in words) \
            and not re.match(r'^(Sœur|Soeur|Père|Frère|Mme|M\.)', txt):
        return ''
    return ' '.join(words[:4]).strip(' .-')


def clean_gups(value):
    value = re.sub(r'\s+', ' ', value or '').strip(' ,.;-')
    return ROMAN.get(value.lower(), value)


def main():
    parser = argparse.ArgumentParser(description=__doc__,
                                     formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument('docx', help='Document Word de la liste des CAPE agréés')
    parser.add_argument('-o', '--out', default='capes_agrees_a_valider.csv',
                        help='CSV de sortie (défaut : capes_agrees_a_valider.csv)')
    args = parser.parse_args()

    if not os.path.exists(args.docx):
        raise SystemExit(f'Fichier introuvable : {args.docx}')

    capes = read_docx(args.docx)
    if not capes:
        raise SystemExit('Aucune ligne CAPE exploitable dans le document.')

    # Reprise de l'existant : garderies (absentes du document) et GUPS omis.
    garderies, previous_gups = [], {}
    if os.path.exists(args.out):
        with open(args.out, encoding='utf-8-sig', newline='') as f:
            for row in csv.DictReader(f):
                if row.get('service', '').strip().lower() == 'garderie':
                    garderies.append(row)
                elif row.get('gups', '').strip():
                    previous_gups[row['denomination'].strip()] = row['gups'].strip()

    rows, recovered = [], 0
    for c in capes:
        gups = clean_gups(c['gups'])
        if not gups:
            fallback = previous_gups.get(c['denomination'].strip())
            if fallback:
                gups = clean_gups(fallback)
                recovered += 1

        localisation = c['localisation']
        if gups and gups.lower() not in localisation.lower():
            localisation = f'{localisation}, {gups}' if localisation else gups

        tel = phones(c['contacts'])
        rows.append({
            'numero': c['numero'],
            'denomination': c['denomination'],
            'service': 'CAPE',
            'type_cape': '',
            'departement': c['departement'],
            'commune': '',
            'arrondissement': '',
            'localisation': localisation,
            'gups': gups,
            'telephone1': tel[0] if tel else '',
            'telephone2': tel[1] if len(tel) > 1 else '',
            'email': '',
            'nom_promoteur': promoter(c['contacts']),
            'reference_agrement': '',
            'annee_agrement': '',
        })

    for i, g in enumerate(garderies, start=len(rows) + 1):
        g = dict(g)
        g['numero'] = i
        rows.append(g)

    with open(args.out, 'w', encoding='utf-8-sig', newline='') as f:
        writer = csv.DictWriter(f, fieldnames=FIELDS, quoting=csv.QUOTE_MINIMAL)
        writer.writeheader()
        writer.writerows(rows)

    sans_gups = [r for r in rows[:len(capes)] if not r['gups']]
    print(f'{args.out} : {len(capes)} CAPE + {len(garderies)} garderies = {len(rows)} lignes')
    print(f'  téléphone renseigné : {sum(1 for r in rows[:len(capes)] if r["telephone1"])}/{len(capes)}')
    print(f'  GUPS renseigné      : {len(capes) - len(sans_gups)}/{len(capes)}'
          + (f' (dont {recovered} repris de l\'ancien CSV)' if recovered else ''))
    for r in sans_gups:
        print(f'  ! sans GUPS : [{r["numero"]}] {r["denomination"]} ({r["departement"]})')


if __name__ == '__main__':
    sys.exit(main())
