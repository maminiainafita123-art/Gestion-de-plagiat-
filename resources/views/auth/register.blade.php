<!-- resources/views/auth/register.blade.php -->
@extends('layouts.app')
@section('title', 'Inscription')

@section('content')
<div class="max-w-md mx-auto mt-10">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <i class="fas fa-user-plus text-indigo-600 text-5xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-800">Inscription</h2>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom complet</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    required>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    required>
            </div>

            <div class="mb-4">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                <select name="role" id="role"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    onchange="toggleStudentId(this.value)" required>
                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Étudiant</option>
                    <option value="professor" {{ old('role') == 'professor' ? 'selected' : '' }}>Professeur</option>
                </select>
            </div>

            <div class="mb-4" id="student-id-group">
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Numéro étudiant</label>
                <input type="text" name="student_id" id="student_id" value="{{ old('student_id') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    placeholder="Ex: ETU-2024-001">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
                <input type="password" name="password" id="password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    required>
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    required>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 transition font-semibold">
                <i class="fas fa-user-plus mr-2"></i>S'inscrire
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600">Déjà un compte ?
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Se connecter</a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleStudentId(role) {
    document.getElementById('student-id-group').style.display = role === 'student' ? 'block' : 'none';
}
toggleStudentId(document.getElementById('role').value);
</script>
@endpush
@endsection