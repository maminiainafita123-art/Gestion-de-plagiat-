<!-- resources/views/student/submission/history.blade.php -->
@extends('layouts.app')
@section('title', 'Historique des soumissions')

@section('content')
<div class="mb-6">
    <a href="{{ route('student.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-1"></i>Retour
    </a>
    <h1 class="text-3xl font-bold text-gray-800 mt-2">
        <i class="fas fa-history mr-2 text-indigo-600"></i>Historique des Soumissions
    </h1>
</div>

@if($submissions->isEmpty())
    <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
        <i class="fas fa-inbox text-5xl mb-4"></i>
        <p class="text-xl">Aucune soumission effectuée.</p>
    </div>
@else
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Examen</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Fichier</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Fichiers analysés</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Lignes de code</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Date de soumission</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Statut examen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($submissions as $sub)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $sub->exam->title }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <i class="fas fa-file-archive text-indigo-500 mr-1"></i>{{ $sub->original_filename }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sub->file_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($sub->total_lines) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $sub->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            @if($sub->exam->status === 'open')
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Ouvert</span>
                            @elseif($sub->exam->status === 'analyzed')
                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">Analysé</span>
                            @else
                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Fermé</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection