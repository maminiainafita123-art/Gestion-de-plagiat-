<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\PlagiarismResult;
use App\Models\Submission;
use App\Jobs\RunPlagiarismAnalysis;
use App\Services\PlagiarismAnalysisService;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function launch(Request $request, Exam $exam)
    {
        if ($exam->professor_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'algorithm' => 'required|in:winnowing,jaccard_bloom,both',
        ]);

        $algorithm = $request->input('algorithm', 'both');

        if ($exam->submissions()->count() < 2) {
            return back()->with('error', 'Il faut au moins 2 soumissions pour lancer l\'analyse.');
        }

        // Lancer en arrière-plan ou directement selon la config
        if (config('queue.default') === 'sync') {
            // Exécution synchrone pour le développement
            $service = app(PlagiarismAnalysisService::class);
            $service->analyzeExam($exam, $algorithm);
            return redirect()->route('professor.analysis.results', $exam)
                ->with('success', 'Analyse terminée !');
        }

        RunPlagiarismAnalysis::dispatch($exam->id, $algorithm);

        return back()->with('success', 'Analyse lancée en arrière-plan. Les résultats seront disponibles sous peu.');
    }

    public function results(Exam $exam)
    {
        if ($exam->professor_id !== auth()->id()) {
            abort(403);
        }

        $results = PlagiarismResult::where('exam_id', $exam->id)
            ->with(['submissionA.student', 'submissionB.student'])
            ->orderByDesc('combined_score')
            ->get();

        // Statistiques
        $stats = [
            'total_pairs' => $results->count(),
            'avg_score' => $results->avg('combined_score') ?? 0,
            'max_score' => $results->max('combined_score') ?? 0,
            'min_score' => $results->min('combined_score') ?? 0,
            'high_risk' => $results->where('combined_score', '>=', 70)->count(),
            'medium_risk' => $results->whereBetween('combined_score', [40, 70])->count(),
            'low_risk' => $results->where('combined_score', '<', 40)->count(),
        ];

        return view('professor.analysis.results', compact('exam', 'results', 'stats'));
    }

    public function compare(Exam $exam, Submission $submissionA, Submission $submissionB)
    {
        if ($exam->professor_id !== auth()->id()) {
            abort(403);
        }

        $result = PlagiarismResult::where(function ($query) use ($submissionA, $submissionB) {
            $query->where('submission_a_id', $submissionA->id)
                  ->where('submission_b_id', $submissionB->id);
        })->orWhere(function ($query) use ($submissionA, $submissionB) {
            $query->where('submission_a_id', $submissionB->id)
                  ->where('submission_b_id', $submissionA->id);
        })->first();

        $submissionA->load('student');
        $submissionB->load('student');

        // Préparer le contenu pour la vue côte à côte
        $contentA = $submissionA->concatenated_content ?? '';
        $contentB = $submissionB->concatenated_content ?? '';

        // Positions correspondantes pour le surlignage
        $matchedPositions = $result ? ($result->matched_positions ?? ['a' => [], 'b' => []]) : ['a' => [], 'b' => []];

        return view('professor.analysis.compare', compact(
            'exam', 'submissionA', 'submissionB', 'result',
            'contentA', 'contentB', 'matchedPositions'
        ));
    }
}