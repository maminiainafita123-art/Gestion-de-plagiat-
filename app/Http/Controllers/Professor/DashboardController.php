<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\Exam;

class DashboardController extends Controller
{
    public function index()
    {
        $exams = Exam::where('professor_id', auth()->id())
            ->withCount('submissions')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('professor.dashboard', compact('exams'));
    }
}