<?php

namespace App\Services;

/**
 * Algorithme de Winnowing.
 *
 * PARAMÈTRES AJUSTÉS pour les petits fichiers :
 * - kgramSize réduit à 3 (au lieu de 10) pour détecter les similitudes
 *   dans de courts textes (5-10 lignes)
 * - windowSize réduit à 3 (au lieu de 6)
 *
 * Pour la production avec de vrais projets web (>200 lignes) :
 * - Remettre kgramSize=10, windowSize=6
 */
class WinnowingService
{
    private int $kgramSize;
    private int $windowSize;

    public function __construct(int $kgramSize = 5, int $windowSize = 4)
    {
        $this->kgramSize = $kgramSize;
        $this->windowSize = $windowSize;
    }

    /**
     * Générer les fingerprints via Winnowing.
     */
    public function generateFingerprints(string $text): array
    {
        $text = trim($text);

        if (strlen($text) < $this->kgramSize) {
            // Texte trop court : utiliser des hashes simples de mots
            return $this->generateSimpleHashes($text);
        }

        $hashes = $this->computeKgramHashes($text);

        if (count($hashes) === 0) {
            return [];
        }

        if (count($hashes) < $this->windowSize) {
            // Pas assez de hashes : retourner tous les hashes
            $fingerprints = [];
            foreach ($hashes as $pos => $hash) {
                $fingerprints[] = ['hash' => $hash, 'position' => $pos];
            }
            return $fingerprints;
        }

        return $this->applyWinnowing($hashes);
    }

    /**
     * Pour les textes très courts, générer des hashes par mots/tokens.
     */
    private function generateSimpleHashes(string $text): array
    {
        $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $fingerprints = [];

        foreach ($tokens as $pos => $token) {
            if (!empty($token)) {
                $fingerprints[] = [
                    'hash'     => abs(crc32($token)),
                    'position' => $pos,
                ];
            }
        }

        return $fingerprints;
    }

    /**
     * Calculer les hashes de tous les k-grams.
     */
    private function computeKgramHashes(string $text): array
    {
        $hashes = [];
        $length = strlen($text) - $this->kgramSize + 1;

        if ($length <= 0) {
            return $hashes;
        }

        for ($i = 0; $i < $length; $i++) {
            $kgram = substr($text, $i, $this->kgramSize);
            $hashes[$i] = abs(crc32($kgram));
        }

        return $hashes;
    }

    /**
     * Appliquer le winnowing : fenêtre glissante, sélectionner le minimum.
     */
    private function applyWinnowing(array $hashes): array
    {
        $fingerprints = [];
        $hashValues   = array_values($hashes);
        $hashPositions = array_keys($hashes);
        $n = count($hashValues);
        $previousMinPos = -1;

        for ($i = 0; $i <= $n - $this->windowSize; $i++) {
            $minVal = PHP_INT_MAX;
            $minPos = -1;

            // Chercher le minimum (le plus à droite en cas d'égalité)
            for ($j = $this->windowSize - 1; $j >= 0; $j--) {
                if ($hashValues[$i + $j] <= $minVal) {
                    $minVal = $hashValues[$i + $j];
                    $minPos = $hashPositions[$i + $j];
                }
            }

            if ($minPos !== $previousMinPos) {
                $fingerprints[] = [
                    'hash'     => $minVal,
                    'position' => $minPos,
                ];
                $previousMinPos = $minPos;
            }
        }

        return $fingerprints;
    }

    /**
     * Calculer le score de similarité entre deux ensembles de fingerprints.
     */
    public function calculateSimilarity(array $fpA, array $fpB): array
    {
        if (empty($fpA) || empty($fpB)) {
            return [
                'score'            => 0.0,
                'matched_hashes'   => [],
                'matched_positions' => ['a' => [], 'b' => []],
            ];
        }

        $setA = array_unique(array_column($fpA, 'hash'));
        $setB = array_unique(array_column($fpB, 'hash'));

        $setAFlip = array_flip($setA);
        $setBFlip = array_flip($setB);

        $intersection = array_intersect($setA, $setB);
        $union        = array_unique(array_merge($setA, $setB));

        $score = count($union) > 0
            ? round((count($intersection) / count($union)) * 100, 2)
            : 0.0;

        // Positions correspondantes
        $intersectionFlip = array_flip($intersection);
        $matchedA = [];
        $matchedB = [];

        foreach ($fpA as $fp) {
            if (isset($intersectionFlip[$fp['hash']])) {
                $matchedA[] = $fp['position'];
            }
        }
        foreach ($fpB as $fp) {
            if (isset($intersectionFlip[$fp['hash']])) {
                $matchedB[] = $fp['position'];
            }
        }

        return [
            'score'             => $score,
            'matched_hashes'    => array_values($intersection),
            'matched_positions' => ['a' => $matchedA, 'b' => $matchedB],
        ];
    }

    public function getKgramSize(): int  { return $this->kgramSize; }
    public function getWindowSize(): int { return $this->windowSize; }
}