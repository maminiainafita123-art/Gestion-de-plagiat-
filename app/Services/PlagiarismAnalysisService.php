<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Submission;
use App\Models\Fingerprint;
use App\Models\PlagiarismResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlagiarismAnalysisService
{
    private FileProcessorService $fileProcessor;
    private WinnowingService $winnowing;
    private JaccardBloomService $jaccardBloom;

    public function __construct(
        FileProcessorService $fileProcessor,
        WinnowingService $winnowing,
        JaccardBloomService $jaccardBloom
    ) {
        $this->fileProcessor = $fileProcessor;
        $this->winnowing = $winnowing;
        $this->jaccardBloom = $jaccardBloom;
    }

    /**
     * Lancer l'analyse complète pour un examen.
     *
     * @param Exam $exam
     * @param string $algorithm 'winnowing', 'jaccard_bloom', ou 'both'
     */
    public function analyzeExam(Exam $exam, string $algorithm = 'both'): void
    {
        $submissions = $exam->submissions()->get();

        if ($submissions->count() < 2) {
            Log::info("Pas assez de soumissions pour l'examen {$exam->id}");
            return;
        }

        // Supprimer les anciens résultats
        PlagiarismResult::where('exam_id', $exam->id)->delete();

        // Étape 1 : Générer les empreintes pour chaque soumission
        foreach ($submissions as $submission) {
            $this->generateFingerprintsForSubmission($submission, $algorithm);
        }

        // Étape 2 : Comparer chaque paire
        $submissionArray = $submissions->values()->all();
        $count = count($submissionArray);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $this->compareSubmissions(
                    $exam,
                    $submissionArray[$i],
                    $submissionArray[$j],
                    $algorithm
                );
            }
        }

        // Mettre à jour le statut de l'examen
        $exam->update(['status' => 'analyzed']);

        Log::info("Analyse terminée pour l'examen {$exam->id}. " .
            ($count * ($count - 1) / 2) . " paires comparées.");
    }

    /**
     * Générer les empreintes pour une soumission donnée.
     */
    public function generateFingerprintsForSubmission(Submission $submission, string $algorithm): void
    {
        // Supprimer les anciennes empreintes
        Fingerprint::where('submission_id', $submission->id)->delete();

        $content = $submission->concatenated_content;
        if (empty($content)) {
            return;
        }

        $normalizedContent = $this->fileProcessor->normalizeCode($content);

        if (empty($normalizedContent)) {
            return;
        }

        $fingerprintsToInsert = [];

        // Winnowing
        if ($algorithm === 'winnowing' || $algorithm === 'both') {
            $winnowingFPs = $this->winnowing->generateFingerprints($normalizedContent);

            foreach ($winnowingFPs as $fp) {
                $fingerprintsToInsert[] = [
                    'submission_id' => $submission->id,
                    'hash_value' => $fp['hash'],
                    'position' => $fp['position'],
                    'line_number' => $this->positionToLine($content, $fp['position']),
                    'algorithm' => 'winnowing',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Jaccard Bloom
        if ($algorithm === 'jaccard_bloom' || $algorithm === 'both') {
            $jaccardFPs = $this->jaccardBloom->generateFingerprints($normalizedContent);

            foreach ($jaccardFPs as $fp) {
                $fingerprintsToInsert[] = [
                    'submission_id' => $submission->id,
                    'hash_value' => $fp['hash'],
                    'position' => $fp['position'],
                    'line_number' => $this->positionToLine($content, $fp['position']),
                    'algorithm' => 'jaccard_bloom',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insertion par lots pour les performances
        foreach (array_chunk($fingerprintsToInsert, 500) as $chunk) {
            Fingerprint::insert($chunk);
        }
    }

    /**
     * Comparer deux soumissions et enregistrer le résultat.
     */
    public function compareSubmissions(
        Exam $exam,
        Submission $subA,
        Submission $subB,
        string $algorithm
    ): PlagiarismResult {
        $contentA = $this->fileProcessor->normalizeCode($subA->concatenated_content ?? '');
        $contentB = $this->fileProcessor->normalizeCode($subB->concatenated_content ?? '');

        $winnowingScore = null;
        $jaccardScore = null;
        $combinedScore = 0;
        $matchedPositions = ['a' => [], 'b' => []];

        // Calcul Winnowing
        if ($algorithm === 'winnowing' || $algorithm === 'both') {
            $fpA = $this->winnowing->generateFingerprints($contentA);
            $fpB = $this->winnowing->generateFingerprints($contentB);
            $winnowingResult = $this->winnowing->calculateSimilarity($fpA, $fpB);
            $winnowingScore = $winnowingResult['score'];
            $matchedPositions = $winnowingResult['matched_positions'];
        }

        // Calcul Jaccard Bloom
        if ($algorithm === 'jaccard_bloom' || $algorithm === 'both') {
            $jaccardResult = $this->jaccardBloom->calculateSimilarity($contentA, $contentB);
            $jaccardScore = $jaccardResult['score'];

            if ($algorithm === 'jaccard_bloom') {
                $matchedPositions = $jaccardResult['matched_positions'];
            }
        }

        // Score combiné
        if ($algorithm === 'both' && $winnowingScore !== null && $jaccardScore !== null) {
            // Moyenne pondérée : 60% Winnowing, 40% Jaccard
            $combinedScore = round(($winnowingScore * 0.6) + ($jaccardScore * 0.4), 2);
        } elseif ($winnowingScore !== null) {
            $combinedScore = $winnowingScore;
        } elseif ($jaccardScore !== null) {
            $combinedScore = $jaccardScore;
        }

        // Enregistrer le résultat
        return PlagiarismResult::updateOrCreate(
            [
                'submission_a_id' => $subA->id,
                'submission_b_id' => $subB->id,
                'algorithm_used' => $algorithm,
            ],
            [
                'exam_id' => $exam->id,
                'winnowing_score' => $winnowingScore,
                'jaccard_bloom_score' => $jaccardScore,
                'combined_score' => $combinedScore,
                'matched_positions' => $matchedPositions,
            ]
        );
    }

    /**
     * Convertir une position dans le texte normalisé en numéro de ligne
     * dans le texte original.
     */
    private function positionToLine(string $originalContent, int $position): int
    {
        $substring = substr($originalContent, 0, min($position, strlen($originalContent)));
        return substr_count($substring, "\n") + 1;
    }
}