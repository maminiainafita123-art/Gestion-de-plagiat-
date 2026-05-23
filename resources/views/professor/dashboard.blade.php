<!-- resources/views/professor/dashboard.blade.php -->
@extends('layouts.app')
@section('title', 'Tableau de bord professeur')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-chalkboard-teacher mr-2 text-indigo-600"></i>Tableau de bord Professeur
        </h1>
        <p class="text-gray-500 mt-1">Gérez vos examens et analysez les soumissions</p>
    </div>
    <a href="{{ route('professor.exam.create') }}"
        class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition font-semibold">
        <i class="fas fa-plus mr-2"></i>Nouvel Examen
    </a>
</div>

<!-- Stats rapides -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="bg-indigo-100 rounded-full p-3 mr-4">
                <i class="fas fa-book text-indigo-600 text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $exams->count() }}</p>
                <p class="text-gray-500 text-sm">Examens créés</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="bg-green-100 rounded-full p-3 mr-4">
                <i class="fas fa-door-open text-green-600 text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $exams->where('status', 'open')->count() }}</p>
                <p class="text-gray-500 text-sm">Examens ouverts</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="bg-blue-100 rounded-full p-3 mr-4">
                <i class="fas fa-file-upload text-blue-600 text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $exams->sum('submissions_count') }}</p>
                <p class="text-gray-500 text-sm">Total soumissions</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="bg-purple-100 rounded-full p-3 mr-4">
                <i class="fas fa-chart-bar text-purple-600 text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $exams->where('status', 'analyzed')->count() }}</p>
                <p class="text-gray-500 text-sm">Examens analysés</p>
            </div>
        </div>
    </div>
</div>

<!-- Liste des examens -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-700">Mes Examens</h2>
    </div>

    @if($exams->isEmpty())
        <div class="p-12 text-center text-gray-500">
            <i class="fas fa-folder-open text-5xl mb-4"></i>
            <p class="text-xl">Aucun examen créé.</p>
            <a href="{{ route('professor.exam.create') }}" class="text-indigo-600 hover:text-indigo-800 mt-2 inline-block">
                Créer votre premier examen
            </a>
        </div>
    @else
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Titre</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Date limite</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Extensions</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Soumissions</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Statut</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($exams as $exam)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('professor.exam.show', $exam) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                {{ $exam->title }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $exam->deadline->format('d/m/Y H:i') }}
                            @if($exam->deadline->isPast())
                                <span class="text-red-500 text-xs ml-1">(expiré)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @foreach($exam->allowed_extensions as $ext)
                                <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs mr-1">{{ $ext }}</span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                            {{ $exam->submissions_count }}
                        </td>
                        <td class="px-6 py-4">
                            @if($exam->status === 'open')
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-semibold">Ouvert</span>
                            @elseif($exam->status === 'analyzed')
                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-semibold">Analysé</span>
                            @else
                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-semibold">Fermé</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex space-x-2">
                                <a href="{{ route('professor.exam.show', $exam) }}"
                                    class="text-indigo-600 hover:text-indigo-800" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('professor.exam.edit', $exam) }}"
                                    class="text-yellow-600 hover:text-yellow-800" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($exam->status === 'analyzed')
                                    <a href="{{ route('professor.analysis.results', $exam) }}"
                                        class="text-green-600 hover:text-green-800" title="Résultats">
                                        <i class="fas fa-chart-pie"></i>
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('professor.exam.destroy', $exam) }}"
                                    onsubmit="return confirm('Supprimer cet examen ?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection