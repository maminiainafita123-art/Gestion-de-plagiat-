<!-- resources/views/auth/login.blade.php -->
@extends('layouts.app')
@section('title', 'Connexion')

@section('content')
<div class="max-w-md mx-auto mt-10">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <i class="fas fa-shield-alt text-indigo-600 text-5xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-800">Connexion</h2>
            <p class="text-gray-500">Système de Détection de Plagiat</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-1"></i>Email
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="votre@email.com" required autofocus>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-1"></i>Mot de passe
                </label>
                <input type="password" name="password" id="password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="••••••••" required>
            </div>

            <div class="flex items-center mb-6">
                <input type="checkbox" name="remember" id="remember"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="remember" class="ml-2 text-sm text-gray-600">Se souvenir de moi</label>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 transition font-semibold">
                <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600">Pas encore de compte ?
                <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                    S'inscrire
                </a>
            </p>
        </div>
    </div>
</div>
@endsection