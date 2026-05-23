<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExamRequest;
use App\Models\Exam;

class ExamController extends Controller
{
    public function create()
    {
        return view('professor.exam.create');
    }

    public function store(StoreExamRequest $request)
    {
        $excludedFilenames = [];
        if ($request->excluded_filenames) {
            $excludedFilenames = array_map('trim', explode(',', $request->excluded_filenames));
            $excludedFilenames = array_filter($excludedFilenames);
        }

        Exam::create([
            'professor_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'allowed_extensions' => $request->allowed_extensions,
            'excluded_filenames' => $excludedFilenames,
            'status' => 'open',
        ]);

        return redirect()->route('professor.dashboard')
            ->with('success', 'Examen créé avec succès !');
    }

    public function show(Exam $exam)
    {
        $this->authorizeExam($exam);

        $exam->load(['submissions.student', 'plagiarismResults']);

        return view('professor.exam.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        $this->authorizeExam($exam);
        return view('professor.exam.edit', compact('exam'));
    }

    public function update(StoreExamRequest $request, Exam $exam)
    {
        $this->authorizeExam($exam);

        $excludedFilenames = [];
        if ($request->excluded_filenames) {
            $excludedFilenames = array_map('trim', explode(',', $request->excluded_filenames));
            $excludedFilenames = array_filter($excludedFilenames);
        }

        $exam->update([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'allowed_extensions' => $request->allowed_extensions,
            'excluded_filenames' => $excludedFilenames,
        ]);

        return redirect()->route('professor.exam.show', $exam)
            ->with('success', 'Examen mis à jour.');
    }

    public function destroy(Exam $exam)
    {
        $this->authorizeExam($exam);
        $exam->delete();

        return redirect()->route('professor.dashboard')
            ->with('success', 'Examen supprimé.');
    }

    private function authorizeExam(Exam $exam): void
    {
        if ($exam->professor_id !== auth()->id()) {
            abort(403);
        }
    }
}