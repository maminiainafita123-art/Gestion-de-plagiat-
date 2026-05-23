<?php

namespace App\Services;

/**
 * Jaccard avec filtre de Bloom.
 *
 * PARAMÈTRES AJUSTÉS pour petits fichiers :
 * - ngramSize réduit à 2 (au lieu de 5)
 * - bloomSize réduit à 512 (moins de mémoire pour de petits textes)
 */
class JaccardBloomService
{
    private int $ngramSize;
    private int $bloomSize;
    private int $hashCount;

    public function __construct(
        int $ngramSize = 3,
        int $bloomSize = 512,
        int $hashCount = 3
    ) {
        $this->ngramSize = $ngramSize;
        $this->bloomSize = $bloomSize;
        $this->hashCount = $hashCount;
    }

    /**
     * Générer les fingerprints (positions de bits dans le bloom filter).
     */
    public function generateFingerprints(string $text): array
    {
        $text = trim($text);

        if (strlen($text) < $this->ngramSize) {
            return $this->generateTokenFingerprints($text);
        }

        $fingerprints = [];
        $bloomFilter  = array_fill(0, $this->bloomSize, 0);
        $length       = strlen($text) - $this->ngramSize + 1;

        for ($i = 0; $i < $length; $i++) {
            $ngram      = substr($text, $i, $this->ngramSize);
            $bitPositions = $this->getBloomPositions($ngram);

            foreach ($bitPositions as $bitPos) {
                if ($bloomFilter[$bitPos] === 0) {
                    $bloomFilter[$bitPos] = 1;
                    $fingerprints[] = [
                        'hash'     => $bitPos,
                        'position' => $i,
                    ];
                }
            }
        }

        return $fingerprints;
    }

    /**
     * Pour les textes très courts : fingerprints par tokens.
     */
    private function generateTokenFingerprints(string $text): array
    {
        $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $fingerprints = [];
        $bloomFilter  = array_fill(0, $this->bloomSize, 0);

        foreach ($tokens as $pos => $token) {
            $bitPositions = $this->getBloomPositions($token);
            foreach ($bitPositions as $bitPos) {
                if ($bloomFilter[$bitPos] === 0) {
                    $bloomFilter[$bitPos] = 1;
                    $fingerprints[] = [
                        'hash'     => $bitPos,
                        'position' => $pos,
                    ];
                }
            }
        }

        return $fingerprints;
    }

    /**
     * Générer le vecteur de bits complet du filtre de Bloom.
     */
    public function generateBloomFilter(string $text): array
    {
        $bloomFilter = array_fill(0, $this->bloomSize, 0);
        $text = trim($text);

        if (empty($text)) {
            return $bloomFilter;
        }

        // Si texte trop court : utiliser les tokens
        if (strlen($text) < $this->ngramSize) {
            $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($tokens as $token) {
                foreach ($this->getBloomPositions($token) as $bitPos) {
                    $bloomFilter[$bitPos] = 1;
                }
            }
            return $bloomFilter;
        }

        $length = strlen($text) - $this->ngramSize + 1;
        for ($i = 0; $i < $length; $i++) {
            $ngram = substr($text, $i, $this->ngramSize);
            foreach ($this->getBloomPositions($ngram) as $bitPos) {
                $bloomFilter[$bitPos] = 1;
            }
        }

        return $bloomFilter;
    }

    /**
     * Double hashing : h(i) = (h1 + i * h2) mod m
     */
    private function getBloomPositions(string $ngram): array
    {
        $positions = [];
        $h1 = abs(crc32($ngram));
        $h2 = abs(crc32(strrev($ngram) . 'bloom_salt_2024'));

        // Éviter h2 = 0
        if ($h2 === 0) {
            $h2 = 1;
        }

        for ($i = 0; $i < $this->hashCount; $i++) {
            $positions[] = ($h1 + $i * $h2) % $this->bloomSize;
        }

        return $positions;
    }

    /**
     * Calculer le score de Jaccard entre deux filtres de Bloom.
     */
    public function calculateJaccardFromBlooms(array $bloomA, array $bloomB): float
    {
        $intersection = 0;
        $union        = 0;

        $size = min(count($bloomA), count($bloomB), $this->bloomSize);

        for ($i = 0; $i < $size; $i++) {
            $a = $bloomA[$i] ?? 0;
            $b = $bloomB[$i] ?? 0;

            if ($a === 1 && $b === 1) {
                $intersection++;
            }
            if ($a === 1 || $b === 1) {
                $union++;
            }
        }

        if ($union === 0) {
            return 0.0;
        }

        return round(($intersection / $union) * 100, 2);
    }

    /**
     * Pipeline complet : texte → bloom → score Jaccard.
     */
    public function calculateSimilarity(string $textA, string $textB): array
    {
        $textA = trim($textA);
        $textB = trim($textB);

        if (empty($textA) || empty($textB)) {
            return [
                'score'             => 0.0,
                'matched_positions' => ['a' => [], 'b' => []],
                'bloom_stats'       => ['bits_a' => 0, 'bits_b' => 0],
            ];
        }

        $bloomA = $this->generateBloomFilter($textA);
        $bloomB = $this->generateBloomFilter($textB);

        $score = $this->calculateJaccardFromBlooms($bloomA, $bloomB);

        $matchedPositions = $this->findMatchedPositions($textA, $textB);

        return [
            'score'             => $score,
            'matched_positions' => $matchedPositions,
            'bloom_stats'       => [
                'bits_a'     => array_sum($bloomA),
                'bits_b'     => array_sum($bloomB),
                'bloom_size' => $this->bloomSize,
            ],
        ];
    }

    /**
     * Trouver les positions des n-grams communs.
     */
    private function findMatchedPositions(string $textA, string $textB): array
    {
        $matchedA = [];
        $matchedB = [];

        // Cas texte court : comparaison par tokens
        if (strlen($textA) < $this->ngramSize || strlen($textB) < $this->ngramSize) {
            $tokensA = preg_split('/\s+/', $textA, -1, PREG_SPLIT_NO_EMPTY);
            $tokensB = preg_split('/\s+/', $textB, -1, PREG_SPLIT_NO_EMPTY);
            $setB = array_flip($tokensB);

            foreach ($tokensA as $pos => $token) {
                if (isset($setB[$token])) {
                    $matchedA[] = $pos;
                }
            }

            $setA = array_flip($tokensA);
            foreach ($tokensB as $pos => $token) {
                if (isset($setA[$token])) {
                    $matchedB[] = $pos;
                }
            }

            return ['a' => $matchedA, 'b' => $matchedB];
        }

        // Cas normal : comparaison par n-grams
        $ngramsB = [];
        $lenB = strlen($textB) - $this->ngramSize + 1;
        for ($i = 0; $i < $lenB; $i++) {
            $ngramsB[substr($textB, $i, $this->ngramSize)] = true;
        }

        $lenA = strlen($textA) - $this->ngramSize + 1;
        for ($i = 0; $i < $lenA; $i++) {
            if (isset($ngramsB[substr($textA, $i, $this->ngramSize)])) {
                $matchedA[] = $i;
            }
        }

        $ngramsA = [];
        for ($i = 0; $i < $lenA; $i++) {
            $ngramsA[substr($textA, $i, $this->ngramSize)] = true;
        }
        for ($i = 0; $i < $lenB; $i++) {
            if (isset($ngramsA[substr($textB, $i, $this->ngramSize)])) {
                $matchedB[] = $i;
            }
        }

        return ['a' => $matchedA, 'b' => $matchedB];
    }

    public function getNgramSize(): int  { return $this->ngramSize; }
    public function getBloomSize(): int  { return $this->bloomSize; }
    public function getHashCount(): int  { return $this->hashCount; }
}