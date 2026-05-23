<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Services\PlagiarismAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunPlagiarismAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes max
    public int $tries = 1;

    private int $examId;
    private string $algorithm;

    public function __construct(int $examId, string $algorithm = 'both')
    {
        $this->examId = $examId;
        $this->algorithm = $algorithm;
    }

    public function handle(PlagiarismAnalysisService $analysisService): void
    {
        $exam = Exam::findOrFail($this->examId);

        Log::info("Début de l'analyse de plagiat pour l'examen: {$exam->title} (ID: {$exam->id})");
        Log::info("Algorithme: {$this->algorithm}");

        try {
            $analysisService->analyzeExam($exam, $this->algorithm);
            Log::info("Analyse terminée avec succès pour l'examen: {$exam->id}");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'analyse: " . $e->getMessage());
            $exam->update(['status' => 'open']);
            throw $e;
        }
    }
}