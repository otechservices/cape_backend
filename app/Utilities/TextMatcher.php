<?php

namespace App\Utilities;

/**
 * Rapprochement textuel tolérant, utilisé par les imports pour relier des
 * libellés saisis à la main (« Boucoumbé », « Zagnanando », « Dassa ») aux
 * entrées réelles de la base (« Boukoumbé », « Zagnanado », « Dassa-Zoumè »).
 *
 * La comparaison ignore les accents, la casse et la ponctuation, puis tolère
 * les fautes de frappe via une distance de Levenshtein normalisée.
 */
class TextMatcher
{
    /** En deçà de ce score, on considère qu'il n'y a pas de correspondance. */
    public const DEFAULT_THRESHOLD = 0.82;

    /**
     * Réduit une chaîne à sa forme comparable : sans accent, en minuscules,
     * ponctuation et espaces multiples ramenés à un espace simple.
     */
    public static function normalize(?string $value): string
    {
        $value = (string) $value;

        // ext-intl n'est pas garantie sur le serveur : repli sur iconv, qui
        // translittère lui aussi les caractères accentués en ASCII.
        if (class_exists(\Normalizer::class)) {
            $decomposed = \Normalizer::normalize($value, \Normalizer::FORM_D);
            if ($decomposed !== false) {
                $value = preg_replace('/\p{Mn}/u', '', $decomposed);
            }
        } else {
            $translit = @iconv('UTF-8', 'ASCII//TRANSLIT', $value);
            if ($translit !== false) {
                $value = $translit;
            }
        }

        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value);

        return trim($value);
    }

    /**
     * Similarité entre deux libellés, entre 0 (rien à voir) et 1 (identiques).
     */
    public static function similarity(string $a, string $b): float
    {
        $a = self::normalize($a);
        $b = self::normalize($b);

        if ($a === '' || $b === '') {
            return 0.0;
        }

        if ($a === $b) {
            return 1.0;
        }

        $distance = levenshtein($a, $b);
        $longest = max(strlen($a), strlen($b));

        return $longest === 0 ? 0.0 : 1 - ($distance / $longest);
    }

    /**
     * Choisit, parmi $candidates (libellé => identifiant), celui qui correspond
     * le mieux à $needle. Renvoie null si aucun ne dépasse le seuil.
     *
     * Trois passes, de la plus sûre à la plus tolérante :
     *  1. égalité une fois normalisé ;
     *  2. le libellé candidat apparaît tel quel dans le texte cherché — c'est
     *     le cas d'une localisation libre du type « Vodjè, Cotonou » ;
     *  3. proximité orthographique.
     *
     * @param  array<string, int>  $candidates
     */
    public static function bestMatch(
        ?string $needle,
        array $candidates,
        float $threshold = self::DEFAULT_THRESHOLD
    ): ?int {
        $needleNorm = self::normalize($needle);

        if ($needleNorm === '' || $candidates === []) {
            return null;
        }

        foreach ($candidates as $label => $id) {
            if (self::normalize((string) $label) === $needleNorm) {
                return $id;
            }
        }

        // Le candidat le plus long d'abord : « Dassa-Zoumè » doit l'emporter
        // sur « Dassa » si les deux existent.
        $byLength = $candidates;
        uksort($byLength, fn ($a, $b) => strlen((string) $b) <=> strlen((string) $a));

        foreach ($byLength as $label => $id) {
            $labelNorm = self::normalize((string) $label);
            if ($labelNorm !== '' && str_contains($needleNorm, $labelNorm)) {
                return $id;
            }
        }

        // Rapprochement orthographique, mot à mot : la localisation est du texte
        // libre (« Boucoumbé, KOUSSOCOUANGO, quartier Zongo »), comparer le
        // candidat à la chaîne entière ferait exploser la distance. On confronte
        // donc chaque mot du candidat à chaque mot du texte.
        $best = null;
        $bestScore = $threshold;

        $needleWords = self::significantWords($needleNorm);

        foreach ($candidates as $label => $id) {
            $score = 0.0;

            foreach (self::significantWords(self::normalize((string) $label)) as $candidateWord) {
                foreach ($needleWords as $needleWord) {
                    $score = max($score, self::similarity($candidateWord, $needleWord));
                }
            }

            if ($score >= $bestScore) {
                $bestScore = $score;
                $best = $id;
            }
        }

        return $best;
    }

    /**
     * Mots porteurs de sens d'un libellé déjà normalisé. Les mots courts sont
     * écartés : ils produiraient des rapprochements fortuits.
     *
     * @return string[]
     */
    private static function significantWords(string $normalized): array
    {
        return array_values(array_filter(
            explode(' ', $normalized),
            fn ($word) => strlen($word) >= 4
        ));
    }
}
