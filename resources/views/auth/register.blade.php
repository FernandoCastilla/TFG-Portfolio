@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 mt-10 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-teal-600 to-teal-400"></div>

    <div class="text-center mb-8 mt-4">
        <h1 class="text-3xl font-extrabold text-gray-800 dark:text-white">Crear Cuenta</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2">Regístrate para acceder al UGR Portfolio</p>
    </div>

    <form method="POST" action="{{ route('registro') }}" class="space-y-6">
        @csrf
        <div>
            <label for="name" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nombre completo</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl py-3 px-4 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:bg-white dark:focus:bg-gray-600 transition-colors">
            @error('name') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Correo electrónico UGR</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl py-3 px-4 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:bg-white dark:focus:bg-gray-600 transition-colors">
            @error('email') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Contraseña</label>
            <input id="password" type="password" name="password" required
                class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl py-3 px-4 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:bg-white dark:focus:bg-gray-600 transition-colors">
            @error('password') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Confirmar Contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl py-3 px-4 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:bg-white dark:focus:bg-gray-600 transition-colors">
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center py-3 px-4 rounded-xl shadow-md text-base font-bold text-white bg-teal-700 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all transform hover:-translate-y-0.5">
                Completar Registro
            </button>
        </div>
    </form>

    <div class="mt-8 text-center border-t border-gray-100 dark:border-gray-700 pt-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            ¿Ya tienes una cuenta?
            <a href="{{ url('/login') }}" class="font-bold text-teal-700 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 transition-colors">Inicia sesión aquí</a>
        </p>
    </div>
</div>
@endsection