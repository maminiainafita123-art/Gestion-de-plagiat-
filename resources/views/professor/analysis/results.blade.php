<!-- resources/views/professor/analysis/results.blade.php -->
@extends('layouts.app')
@section('title', 'Résultats - ' . $exam->title)

@section('content')
<a href="{{ route('professor.exam.show', $exam) }}" class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block">
    <i class="fas fa-arrow-left mr-1"></i>Retour à l'examen
</a>

<h1 class="text-2xl font-bold text-gray-800 mb-6">
    <i class="fas fa-chart-pie mr-2 text-purple-600"></i>Rapport de Similitudes - {{ $exam->title }}
</h1>

<!-- Statistiques globales -->
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-gray-800">{{ $stats['total_pairs'] }}</p>
        <p class="text-xs text-gray-500">Paires comparées</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['avg_score'], 1) }}%</p>
        <p class="text-xs text-gray-500">Score moyen</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['max_score'], 1) }}%</p>
        <p class="text-xs text-gray-500">Score maximum</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center border-l-4 border-red-500">
        <p class="text-2xl font-bold text-red-600">{{ $stats['high_risk'] }}</p>
        <p class="text-xs text-gray-500">Risque élevé (≥70%)</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center border-l-4 border-yellow-500">
        <p class="text-2xl font-bold text-yellow-600">{{ $stats['medium_risk'] }}</p>
        <p class="text-xs text-gray-500">Risque moyen (40-70%)</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center border-l-4 border-green-500">
        <p class="text-2xl font-bold text-green-600">{{ $stats['low_risk'] }}</p>
        <p class="text-xs text-gray-500">Risque faible (&lt;40%)</p>
    </div>
</div>

<!-- Barre de distribution visuelle -->
@if($stats['total_pairs'] > 0)
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h3 class="text-sm font-semibold text-gray-600 mb-3">Distribution des risques</h3>
    <div class="w-full h-6 bg-gray-200 rounded-full overflow-hidden flex">
        @php
            $highPct = ($stats['high_risk'] / $stats['total_pairs']) * 100;
            $medPct = ($stats['medium_risk'] / $stats['total_pairs']) * 100;
            $lowPct = ($stats['low_risk'] / $stats['total_pairs']) * 100;
        @endphp
        <div class="bg-red-500 h-full" style="width: {{ $highPct }}%" title="Élevé: {{ $stats['high_risk'] }}"></div>
        <div class="bg-yellow-400 h-full" style="width: {{ $medPct }}%" title="Moyen: {{ $stats['medium_risk'] }}"></div>
        <div class="bg-green-500 h-full" style="width: {{ $lowPct }}%" title="Faible: {{ $stats['low_risk'] }}"></div>
    </div>
    <div class="flex justify-between mt-2 text-xs text-gray-500">
        <span class="flex items-center"><span class="w-3 h-3 bg-red-500 rounded-full mr-1"></span>Élevé ({{ number_format($highPct, 1) }}%)</span>
        <span class="flex items-center"><span class="w-3 h-3 bg-yellow-400 rounded-full mr-1"></span>Moyen ({{ number_format($medPct, 1) }}%)</span>
        <span class="flex items-center"><span class="w-3 h-3 bg-green-500 rounded-full mr-1"></span>Faible ({{ number_format($lowPct, 1) }}%)</span>
    </div>
</div>
@endif

<!-- Tableau des résultats -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-700">
            Classement par score de similarité (décroissant)
        </h2>
    </div>

    @if($results->isEmpty())
        <div class="p-12 text-center text-gray-500">
            <p>Aucun résultat disponible. Lancez une analyse d'abord.</p>
        </div>
    @else
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">#</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Étudiant A</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Étudiant B</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-600">Winnowing</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-600">Jaccard Bloom</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-600">Score Combiné</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-600">Risque</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($results as $index => $result)
                    @php
                        $riskClass = 'risk-low';
                        $riskLabel = 'Faible';
                        $riskBg = 'bg-green-100 text-green-700';
                        if ($result->combined_score >= 70) {
                            $riskClass = 'risk-high';
                            $riskLabel = 'ÉLEVÉ';
                            $riskBg = 'bg-red-100 text-red-700';
                        } elseif ($result->combined_score >= 40) {
                            $riskClass = 'risk-medium';
                            $riskLabel = 'Moyen';
                            $riskBg = 'bg-yellow-100 text-yellow-700';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $result->combined_score >= 70 ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-gray-800">{{ $result->submissionA->student->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $result->submissionA->student->student_id ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-gray-800">{{ $result->submissionB->student->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $result->submissionB->student->student_id ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($result->winnowing_score !== null)
                                <span class="{{ $riskClass }}">{{ number_format($result->winnowing_score, 1) }}%</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($result->jaccard_bloom_score !== null)
                                <span class="{{ $riskClass }}">{{ number_format($result->jaccard_bloom_score, 1) }}%</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-lg font-bold {{ $riskClass }}">
                                {{ number_format($result->combined_score, 1) }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="{{ $riskBg }} px-2 py-1 rounded text-xs font-bold">
                                {{ $riskLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('professor.analysis.compare', [$exam, $result->submissionA, $result->submissionB]) }}"
                                class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700 transition">
                                <i class="fas fa-columns mr-1"></i>Comparer
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection