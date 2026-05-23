<!-- resources/views/professor/exam/show.blade.php -->
@extends('layouts.app')
@section('title', $exam->title)

@section('content')
<a href="{{ route('professor.dashboard') }}" class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block">
    <i class="fas fa-arrow-left mr-1"></i>Retour au tableau de bord
</a>

<!-- En-tête examen -->
<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $exam->title }}</h1>
            @if($exam->description)
                <p class="text-gray-500 mt-2">{{ $exam->description }}</p>
            @endif
            <div class="flex space-x-6 mt-4 text-sm text-gray-600">
                <span><i class="fas fa-clock mr-1"></i>Limite: {{ $exam->deadline->format('d/m/Y H:i') }}</span>
                <span><i class="fas fa-file-code mr-1"></i>{{ implode(', ', $exam->allowed_extensions) }}</span>
                <span><i class="fas fa-users mr-1"></i>{{ $exam->submissions->count() }} soumissions</span>
                <span>
                    @if($exam->status === 'open')
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Ouvert</span>
                    @elseif($exam->status === 'analyzed')
                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">Analysé</span>
                    @else
                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Fermé</span>
                    @endif
                </span>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('professor.exam.edit', $exam) }}"
                class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition text-sm">
                <i class="fas fa-edit mr-1"></i>Modifier
            </a>
        </div>
    </div>
</div>

<!-- Lancer l'analyse -->
<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">
        <i class="fas fa-search mr-2 text-purple-600"></i>Lancer l'analyse de plagiat
    </h2>

    @if($exam->submissions->count() < 2)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-700">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Il faut au moins 2 soumissions pour lancer une analyse.
            Actuellement : {{ $exam->submissions->count() }} soumission(s).
        </div>
    @else
        <form method="POST" action="{{ route('professor.analysis.launch', $exam) }}" class="flex items-end space-x-4">
            @csrf
            <div class="flex-1">
                <label for="algorithm" class="block text-sm font-medium text-gray-700 mb-2">
                    Algorithme de détection
                </label>
                <select name="algorithm" id="algorithm"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="both">🔀 Les deux (Winnowing + Jaccard Bloom)</option>
                    <option value="winnowing">🏷️ Winnowing uniquement</option>
                    <option value="jaccard_bloom">🌸 Jaccard de Bloom uniquement</option>
                </select>
            </div>
            <button type="submit"
                class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-semibold"
                onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin mr-2\'></i>Analyse en cours...'; this.form.submit();">
                <i class="fas fa-play mr-2"></i>Lancer l'analyse
            </button>
        </form>

        @if($exam->status === 'analyzed')
            <div class="mt-4">
                <a href="{{ route('professor.analysis.results', $exam) }}"
                    class="text-indigo-600 hover:text-indigo-800 font-semibold">
                    <i class="fas fa-chart-pie mr-1"></i>Voir les résultats de la dernière analyse
                </a>
            </div>
        @endif
    @endif
</div>

<!-- Liste des soumissions -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-700">
            <i class="fas fa-file-upload mr-2 text-blue-500"></i>Soumissions ({{ $exam->submissions->count() }})
        </h2>
    </div>

    @if($exam->submissions->isEmpty())
        <div class="p-12 text-center text-gray-500">
            <i class="fas fa-inbox text-4xl mb-4"></i>
            <p>Aucune soumission reçue.</p>
        </div>
    @else
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Étudiant</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Fichier</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Fichiers</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Lignes</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($exam->submissions as $sub)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $sub->student->name }}</p>
                                <p class="text-xs text-gray-500">{{ $sub->student->student_id ?? $sub->student->email }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sub->original_filename }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sub->file_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($sub->total_lines) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $sub->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('professor.submission.show', $sub) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-sm">
                                <i class="fas fa-eye mr-1"></i>Voir
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection