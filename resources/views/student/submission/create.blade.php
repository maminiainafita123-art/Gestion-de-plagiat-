<!-- resources/views/student/submission/create.blade.php -->
@extends('layouts.app')
@section('title', 'Soumettre un projet')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('student.dashboard') }}" class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block">
        <i class="fas fa-arrow-left mr-1"></i>Retour au tableau de bord
    </a>

    <div class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="fas fa-upload mr-2 text-indigo-600"></i>Soumettre un projet
        </h2>
        <p class="text-gray-500 mb-6">Examen : <strong>{{ $exam->title }}</strong></p>

        <!-- Infos examen -->
        <div class="bg-indigo-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Date limite :</span>
                    <span class="font-semibold text-gray-800">{{ $exam->deadline->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Extensions acceptées :</span>
                    <span class="font-semibold text-gray-800">{{ implode(', ', $exam->allowed_extensions) }}</span>
                </div>
            </div>
            @if($exam->excluded_filenames && count($exam->excluded_filenames) > 0)
                <div class="mt-2 text-sm">
                    <span class="text-gray-600">Fichiers exclus :</span>
                    <span class="font-semibold text-red-600">{{ implode(', ', $exam->excluded_filenames) }}</span>
                </div>
            @endif
        </div>

        @if($existingSubmission)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Vous avez déjà soumis "<strong>{{ $existingSubmission->original_filename }}</strong>"
                    le {{ $existingSubmission->created_at->format('d/m/Y à H:i') }}.
                    Un nouveau dépôt remplacera l'ancien.
                </p>
            </div>
        @endif

        <form method="POST" action="{{ route('student.submission.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->id }}">

            <div class="mb-6">
                <label for="zip_file" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-file-archive mr-1"></i>Fichier ZIP du projet
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-500 transition"
                     id="drop-zone">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-500 mb-2">Glissez votre fichier ici ou cliquez pour sélectionner</p>
                    <p class="text-gray-400 text-sm">Format ZIP uniquement - Max 50 MB</p>
                    <input type="file" name="zip_file" id="zip_file" accept=".zip"
                        class="mt-4 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                        file:rounded-lg file:border-0 file:text-sm file:font-semibold
                        file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                </div>
                <p id="file-info" class="mt-2 text-sm text-gray-500 hidden"></p>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 transition font-semibold text-lg">
                <i class="fas fa-paper-plane mr-2"></i>
                {{ $existingSubmission ? 'Resoumettre le projet' : 'Soumettre le projet' }}
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('zip_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const info = document.getElementById('file-info');
    if (file) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        info.textContent = `Fichier sélectionné: ${file.name} (${sizeMB} MB)`;
        info.classList.remove('hidden');
    }
});
</script>
@endpush
@endsection