<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;

class DashboardController extends Controller
{
    public function index()
    {
        $openExams = Exam::where('status', 'open')
            ->where('deadline', '>', now())
            ->orderBy('deadline', 'asc')
            ->get();

        $mySubmissions = auth()->user()->submissions()
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.dashboard', compact('openExams', 'mySubmissions'));
    }
}