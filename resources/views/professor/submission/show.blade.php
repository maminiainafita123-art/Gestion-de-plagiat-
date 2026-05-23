<!-- resources/views/professor/submission/show.blade.php -->
@extends('layouts.app')
@section('title', 'Soumission de ' . $submission->student->name)

@section('content')
<a href="{{ route('professor.exam.show', $submission->exam) }}" class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block">
    <i class="fas fa-arrow-left mr-1"></i>Retour à l'examen
</a>

<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <h1 class="text-2xl font-bold text-gray-800">
        Soumission de {{ $submission->student->name }}
    </h1>
    <div class="grid grid-cols-4 gap-4 mt-4 text-sm text-gray-600">
        <div><strong>Examen :</strong> {{ $submission->exam->title }}</div>
        <div><strong>Fichier :</strong> {{ $submission->original_filename }}</div>
        <div><strong>Fichiers traités :</strong> {{ $submission->file_count }}</div>
        <div><strong>Lignes de code :</strong> {{ number_format($submission->total_lines) }}</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-700">
            <i class="fas fa-code mr-2"></i>Contenu concaténé
        </h2>
    </div>
    <div class="p-4 bg-gray-900 overflow-x-auto max-h-screen">
        <pre class="code-container text-green-400 whitespace-pre-wrap">{{ $submission->concatenated_content }}</pre>
    </div>
</div>
@endsection