<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Exam;
use App\Models\Submission;
use App\Models\Fingerprint;
use App\Services\FileProcessorService;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    private FileProcessorService $fileProcessor;

    public function __construct(FileProcessorService $fileProcessor)
    {
        $this->fileProcessor = $fileProcessor;
    }

    public function create(Exam $exam)
    {
        if (!$exam->isOpen()) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Cet examen est fermé.');
        }

        $existingSubmission = Submission::where('exam_id', $exam->id)
            ->where('student_id', auth()->id())
            ->first();

        return view('student.submission.create', compact('exam', 'existingSubmission'));
    }

    public function store(StoreSubmissionRequest $request)
    {
        $exam = Exam::findOrFail($request->exam_id);

        if (!$exam->isOpen()) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Cet examen est fermé.');
        }

        // Si re-upload, supprimer l'ancien
        $existingSubmission = Submission::where('exam_id', $exam->id)
            ->where('student_id', auth()->id())
            ->first();

        if ($existingSubmission) {
            // Supprimer les anciennes empreintes
            Fingerprint::where('submission_id', $existingSubmission->id)->delete();
            // Supprimer l'ancien fichier
            Storage::delete($existingSubmission->stored_path);
            $existingSubmission->delete();
        }

        // Stocker le fichier ZIP
        $file = $request->file('zip_file');
        $storedPath = $file->store('submissions/' . $exam->id);

        // Traiter le ZIP
        try {
            $processedData = $this->fileProcessor->processZipSubmission($storedPath, $exam);
        } catch (\Exception $e) {
            Storage::delete($storedPath);
            return back()->with('error', 'Erreur lors du traitement du fichier: ' . $e->getMessage());
        }

        // Créer la soumission
        Submission::create([
            'exam_id' => $exam->id,
            'student_id' => auth()->id(),
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'concatenated_content' => $processedData['concatenated_content'],
            'file_count' => $processedData['file_count'],
            'total_lines' => $processedData['total_lines'],
        ]);

        return redirect()->route('student.dashboard')
            ->with('success', 'Projet soumis avec succès ! (' . $processedData['file_count'] . ' fichiers traités)');
    }

    public function history()
    {
        $submissions = auth()->user()->submissions()
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.submission.history', compact('submissions'));
    }
}