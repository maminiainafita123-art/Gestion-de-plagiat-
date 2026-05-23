<!-- resources/views/professor/exam/edit.blade.php -->
@extends('layouts.app')
@section('title', 'Modifier l\'examen')

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('professor.exam.show', $exam) }}" class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block">
        <i class="fas fa-arrow-left mr-1"></i>Retour
    </a>

    <div class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-edit mr-2 text-yellow-500"></i>Modifier l'examen
        </h2>

        <form method="POST" action="{{ route('professor.exam.update', $exam) }}">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                <input type="text" name="title" id="title" value="{{ old('title', $exam->title) }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <div class="mb-5">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('description', $exam->description) }}</textarea>
            </div>

            <div class="mb-5">
                <label for="deadline" class="block text-sm font-medium text-gray-700 mb-2">Date limite *</label>
                <input type="datetime-local" name="deadline" id="deadline"
                    value="{{ old('deadline', $exam->deadline->format('Y-m-d\TH:i')) }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Extensions *</label>
                <div class="grid grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach(['.php', '.html', '.css', '.js', '.py', '.java', '.c', '.cpp', '.h', '.txt', '.xml', '.json'] as $ext)
                        <label class="flex items-center space-x-2 bg-gray-50 p-3 rounded-lg hover:bg-indigo-50 transition cursor-pointer">
                            <input type="checkbox" name="allowed_extensions[]" value="{{ $ext }}"
                                {{ in_array($ext, old('allowed_extensions', $exam->allowed_extensions ?? [])) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            <span class="text-sm font-mono text-gray-700">{{ $ext }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-6">
                <label for="excluded_filenames" class="block text-sm font-medium text-gray-700 mb-2">Fichiers exclus</label>
                <input type="text" name="excluded_filenames" id="excluded_filenames"
                    value="{{ old('excluded_filenames', implode(', ', $exam->excluded_filenames ?? [])) }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <button type="submit"
                class="w-full bg-yellow-500 text-white py-3 rounded-lg hover:bg-yellow-600 transition font-semibold text-lg">
                <i class="fas fa-save mr-2"></i>Enregistrer les modifications
            </button>
        </form>
    </div>
</div>
@endsection