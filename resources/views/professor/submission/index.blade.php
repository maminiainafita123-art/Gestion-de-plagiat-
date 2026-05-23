<!-- resources/views/professor/submission/index.blade.php -->
@extends('layouts.app')
@section('title', 'Soumissions - ' . $exam->title)

@section('content')
<a href="{{ route('professor.exam.show', $exam) }}" class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block">
    <i class="fas fa-arrow-left mr-1"></i>Retour à l'examen
</a>

<h1 class="text-2xl font-bold text-gray-800 mb-6">
    Soumissions pour "{{ $exam->title }}"
</h1>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Étudiant</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Fichier</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Infos</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Date</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($submissions as $sub)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-semibold text-gray-800">{{ $sub->student->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $sub->original_filename }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $sub->file_count }} fichiers / {{ $sub->total_lines }} lignes
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $sub->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('professor.submission.show', $sub) }}" class="text-indigo-600 hover:text-indigo-800">
                            <i class="fas fa-eye"></i> Voir le code
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection