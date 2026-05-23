<!-- resources/views/professor/exam/create.blade.php -->
@extends('layouts.app')
@section('title', 'Créer un examen')

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('professor.dashboard') }}" class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block">
        <i class="fas fa-arrow-left mr-1"></i>Retour
    </a>

    <div class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>Créer un Examen
        </h2>

        <form method="POST" action="{{ route('professor.exam.store') }}">
            @csrf

            <div class="mb-5">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Titre de l'examen *
                </label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    placeholder="Ex: Projet Web PHP - Session 2024" required>
            </div>

            <div class="mb-5">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    placeholder="Instructions pour les étudiants...">{{ old('description') }}</textarea>
            </div>

            <div class="mb-5">
                <label for="deadline" class="block text-sm font-medium text-gray-700 mb-2">
                    Date limite de soumission *
                </label>
                <input type="datetime-local" name="deadline" id="deadline" value="{{ old('deadline') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    required>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Extensions de fichiers à analyser *
                </label>
                <div class="grid grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach(['.php', '.html', '.css', '.js', '.py', '.java', '.c', '.cpp', '.h', '.txt', '.xml', '.json'] as $ext)
                        <label class="flex items-center space-x-2 bg-gray-50 p-3 rounded-lg hover:bg-indigo-50 transition cursor-pointer">
                            <input type="checkbox" name="allowed_extensions[]" value="{{ $ext }}"
                                {{ in_array($ext, old('allowed_extensions', ['.php', '.html', '.css', '.js'])) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <span class="text-sm font-mono text-gray-700">{{ $ext }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-6">
                <label for="excluded_filenames" class="block text-sm font-medium text-gray-700 mb-2">
                    Fichiers/dossiers à exclure
                    <span class="text-gray-400 font-normal">(séparés par des virgules)</span>
                </label>
                <input type="text" name="excluded_filenames" id="excluded_filenames"
                    value="{{ old('excluded_filenames', 'vendor, node_modules, .git, .env') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    placeholder="vendor, node_modules, .git, bootstrap.min.css">
                <p class="text-xs text-gray-400 mt-1">Les fichiers/dossiers contenant ces noms seront ignorés.</p>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 transition font-semibold text-lg">
                <i class="fas fa-save mr-2"></i>Créer l'examen
            </button>
        </form>
    </div>
</div>
@endsection