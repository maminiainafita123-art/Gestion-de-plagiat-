<!-- resources/views/professor/analysis/compare.blade.php -->
@extends('layouts.app')
@section('title', 'Comparaison côte à côte')

@section('content')
<a href="{{ route('professor.analysis.results', $exam) }}" class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block">
    <i class="fas fa-arrow-left mr-1"></i>Retour aux résultats
</a>

<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">
        <i class="fas fa-columns mr-2 text-indigo-600"></i>Comparaison Côte à Côte
    </h1>

    @if($result)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-sm text-gray-500">Algorithme</p>
                <p class="text-lg font-bold text-gray-800">
                    @if($result->algorithm_used === 'both')
                        Winnowing + Jaccard
                    @elseif($result->algorithm_used === 'winnowing')
                        Winnowing
                    @else
                        Jaccard Bloom
                    @endif
                </p>
            </div>
            @if($result->winnowing_score !== null)
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500">Score Winnowing</p>
                    <p class="text-2xl font-bold {{ $result->winnowing_score >= 70 ? 'text-red-600' : ($result->winnowing_score >= 40 ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ number_format($result->winnowing_score, 1) }}%
                    </p>
                </div>
            @endif
            @if($result->jaccard_bloom_score !== null)
                <div class="bg-purple-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500">Score Jaccard Bloom</p>
                    <p class="text-2xl font-bold {{ $result->jaccard_bloom_score >= 70 ? 'text-red-600' : ($result->jaccard_bloom_score >= 40 ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ number_format($result->jaccard_bloom_score, 1) }}%
                    </p>
                </div>
            @endif
            <div class="rounded-lg p-4 text-center {{ $result->combined_score >= 70 ? 'bg-red-100' : ($result->combined_score >= 40 ? 'bg-yellow-100' : 'bg-green-100') }}">
                <p class="text-sm text-gray-500">Score Combiné</p>
                <p class="text-3xl font-bold {{ $result->combined_score >= 70 ? 'text-red-600' : ($result->combined_score >= 40 ? 'text-yellow-600' : 'text-green-600') }}">
                    {{ number_format($result->combined_score, 1) }}%
                </p>
            </div>
        </div>

        <div class="rounded-lg p-4 mb-4
            {{ $result->combined_score >= 70 ? 'bg-red-50 border border-red-200' :
               ($result->combined_score >= 40 ? 'bg-yellow-50 border border-yellow-200' :
               'bg-green-50 border border-green-200') }}">
            @if($result->combined_score >= 70)
                <p class="text-red-700"><i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Risque élevé de plagiat.</strong> Les deux soumissions présentent une très forte similitude.</p>
            @elseif($result->combined_score >= 40)
                <p class="text-yellow-700"><i class="fas fa-exclamation-circle mr-2"></i>
                    <strong>Risque modéré.</strong> Des similitudes notables ont été détectées.</p>
            @else
                <p class="text-green-700"><i class="fas fa-check-circle mr-2"></i>
                    <strong>Faible risque.</strong> Les soumissions sont suffisamment différentes.</p>
            @endif
        </div>
    @endif
</div>

<!-- Vue côte à côte -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Soumission A -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-4 py-3 bg-blue-600 text-white flex justify-between items-center">
            <div>
                <h3 class="font-bold">{{ $submissionA->student->name }}</h3>
                <p class="text-blue-200 text-xs">{{ $submissionA->student->student_id ?? $submissionA->student->email }}</p>
            </div>
            <div class="text-right text-xs text-blue-200">
                <p>{{ $submissionA->original_filename }}</p>
                <p>{{ $submissionA->file_count }} fichiers / {{ $submissionA->total_lines }} lignes</p>
            </div>
        </div>
        <div class="p-3 bg-gray-900 overflow-auto" style="max-height: 70vh;" id="scroll-a">
            <pre class="code-container text-green-400 whitespace-pre-wrap text-xs">{!! \App\Helpers\CodeHighlighter::highlight($contentA, $matchedPositions['a'] ?? []) !!}</pre>
        </div>
    </div>

    <!-- Soumission B -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-4 py-3 bg-orange-600 text-white flex justify-between items-center">
            <div>
                <h3 class="font-bold">{{ $submissionB->student->name }}</h3>
                <p class="text-orange-200 text-xs">{{ $submissionB->student->student_id ?? $submissionB->student->email }}</p>
            </div>
            <div class="text-right text-xs text-orange-200">
                <p>{{ $submissionB->original_filename }}</p>
                <p>{{ $submissionB->file_count }} fichiers / {{ $submissionB->total_lines }} lignes</p>
            </div>
        </div>
        <div class="p-3 bg-gray-900 overflow-auto" style="max-height: 70vh;" id="scroll-b">
            <pre class="code-container text-green-400 whitespace-pre-wrap text-xs">{!! \App\Helpers\CodeHighlighter::highlight($contentB, $matchedPositions['b'] ?? []) !!}</pre>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Synchroniser le défilement des deux panneaux
const scrollA = document.getElementById('scroll-a');
const scrollB = document.getElementById('scroll-b');
let isSyncing = false;

if (scrollA && scrollB) {
    scrollA.addEventListener('scroll', () => {
        if (!isSyncing) {
            isSyncing = true;
            const maxA = scrollA.scrollHeight - scrollA.clientHeight;
            if (maxA > 0) {
                const ratio = scrollA.scrollTop / maxA;
                scrollB.scrollTop = ratio * (scrollB.scrollHeight - scrollB.clientHeight);
            }
            setTimeout(() => isSyncing = false, 10);
        }
    });
    scrollB.addEventListener('scroll', () => {
        if (!isSyncing) {
            isSyncing = true;
            const maxB = scrollB.scrollHeight - scrollB.clientHeight;
            if (maxB > 0) {
                const ratio = scrollB.scrollTop / maxB;
                scrollA.scrollTop = ratio * (scrollA.scrollHeight - scrollA.clientHeight);
            }
            setTimeout(() => isSyncing = false, 10);
        }
    });
}
</script>
@endpush
@endsection