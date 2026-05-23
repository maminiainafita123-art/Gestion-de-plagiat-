<!-- resources/views/student/dashboard.blade.php -->
@extends('layouts.app')
@section('title', 'Tableau de bord étudiant')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">
        <i class="fas fa-graduation-cap mr-2 text-indigo-600"></i>Tableau de bord
    </h1>
    <p class="text-gray-500 mt-1">Bienvenue, {{ auth()->user()->name }}</p>
</div>

<!-- Examens Ouverts -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">
        <i class="fas fa-book-open mr-2 text-green-500"></i>Examens Ouverts
    </h2>

    @if($openExams->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            <i class="fas fa-inbox text-4xl mb-3"></i>
            <p>Aucun examen ouvert pour le moment.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($openExams as $exam)
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $exam->title }}</h3>
                    @if($exam->description)
                        <p class="text-gray-600 text-sm mb-3">{{ Str::limit($exam->description, 100) }}</p>
                    @endif
                    <div class="space-y-2 text-sm text-gray-500 mb-4">
                        <p><i class="fas fa-clock mr-1"></i>
                            Date limite: <span class="font-semibold text-orange-600">{{ $exam->deadline->format('d/m/Y H:i') }}</span>
                        </p>
                        <p><i class="fas fa-file-code mr-1"></i>
                            Extensions: {{ implode(', ', $exam->allowed_extensions) }}
                        </p>
                        <p><i class="fas fa-hourglass-half mr-1"></i>
                            Temps restant:
                            <span class="font-semibold {{ $exam->deadline->diffInHours(now()) < 24 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $exam->deadline->diffForHumans() }}
                            </span>
                        </p>
                    </div>

                    @php
                        $hasSubmitted = $mySubmissions->where('exam_id', $exam->id)->isNotEmpty();
                    @endphp

                    @if($hasSubmitted)
                        <div class="flex space-x-2">
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded text-sm">
                                <i class="fas fa-check mr-1"></i>Déjà soumis
                            </span>
                            <a href="{{ route('student.submission.create', $exam) }}"
                                class="bg-orange-500 text-white px-3 py-1 rounded text-sm hover:bg-orange-600 transition">
                                <i class="fas fa-redo mr-1"></i>Resoumettre
                            </a>
                        </div>
                    @else
                        <a href="{{ route('student.submission.create', $exam) }}"
                            class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-semibold text-sm">
                            <i class="fas fa-upload mr-1"></i>Soumettre mon projet
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Historique rapide -->
<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-700">
            <i class="fas fa-history mr-2 text-blue-500"></i>Mes Soumissions Récentes
        </h2>
        <a href="{{ route('student.submission.history') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
            Voir tout <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>

    @if($mySubmissions->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            <p>Aucune soumission effectuée.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Examen</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Fichier</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Fichiers traités</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($mySubmissions->take(5) as $sub)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-800">{{ $sub->exam->title }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $sub->original_filename }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $sub->file_count }} fichiers ({{ $sub->total_lines }} lignes)</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $sub->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection