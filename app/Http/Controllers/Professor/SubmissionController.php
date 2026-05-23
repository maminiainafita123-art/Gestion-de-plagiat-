<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Submission;

class SubmissionController extends Controller
{
    public function index(Exam $exam)
    {
        if ($exam->professor_id !== auth()->id()) {
            abort(403);
        }

        $submissions = $exam->submissions()
            ->with('student')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('professor.submission.index', compact('exam', 'submissions'));
    }

    public function show(Submission $submission)
    {
        if ($submission->exam->professor_id !== auth()->id()) {
            abort(403);
        }

        $submission->load(['student', 'exam']);

        return view('professor.submission.show', compact('submission'));
    }
}