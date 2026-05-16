@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <x-breadcrumb :links="[
            ['label' => 'Panel Global', 'url' => url('/')],
            ['label' => 'Configuración', 'url' => null],
        ]" />
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
            <h1 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Configuración
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Introduce los tokens de acceso para conectar con OpenProject y el asistente IA.
                Los valores se guardan localmente en el servidor y no se envían a ningún tercero.
            </p>
        </div>

        {{-- Mensajes de estado --}}
        @if(session('success'))
            <div class="mx-6 mt-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mx-6 mt-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300 rounded-xl text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('configuracion.guardar') }}" class="p-6 space-y-6">
            @csrf

            {{-- OpenProject --}}
            <div class="space-y-4">
                <h2 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    OpenProject
                </h2>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        URL de la instancia
                    </label>
                    <input type="url" name="openproject_url"
                           value="{{ $config['OPENPROJECT_URL'] ?? config('services.openproject.url', '') }}"
                           placeholder="https://proyectostic.ugr.es"
                           class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl
                                  bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                  focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm transition">
                    <p class="text-xs text-gray-400 mt-1">La URL base de tu instancia de OpenProject, sin barra final.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Token de API
                    </label>
                    <input type="password" name="openproject_token"
                           value="{{ $config['OPENPROJECT_TOKEN'] ?? '' }}"
                           placeholder="opapi-..."
                           class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl
                                  bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                  focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm transition">
                    <p class="text-xs text-gray-400 mt-1">
                        Genéralo en tu instancia: <em>Mi cuenta → Token de API</em>
                    </p>
                </div>
            </div>

            <hr class="border-gray-100 dark:border-gray-700">

            {{-- Groq --}}
            <div class="space-y-4">
                <h2 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Asistente IA (Groq)
                </h2>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Clave de API de Groq
                    </label>
                    <input type="password" name="groq_api_key"
                           value="{{ $config['GROQ_API_KEY'] ?? '' }}"
                           placeholder="gsk_..."
                           class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl
                                  bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                  focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm transition">
                    <p class="text-xs text-gray-400 mt-1">
                        Regístrate gratis en <a href="https://console.groq.com" target="_blank"
                        class="text-teal-600 hover:underline">console.groq.com</a> para obtener tu clave.
                    </p>
                </div>
            </div>

            <hr class="border-gray-100 dark:border-gray-700">

            {{-- Modo oscuro --}}
            <div class="space-y-4">
                <h2 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Apariencia
                </h2>
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                    <span class="flex items-center gap-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <svg id="icono-luna" class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg id="icono-sol" class="w-4 h-4 text-yellow-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                        </svg>
                        <span id="texto-tema">Modo oscuro</span>
                    </span>
                    <button onclick="toggleTema()" id="btn-tema"
                            aria-label="Activar modo oscuro"
                            type="button"
                            class="focus:outline-none">
                        <div id="pill-tema" class="w-10 h-5 bg-gray-300 dark:bg-gray-600 rounded-full relative transition-colors duration-300">
                            <div id="circulo-tema" class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-all duration-300"></div>
                        </div>
                    </button>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl
                               transition shadow-sm text-sm">
                    Guardar configuración
                </button>
            </div>
        </form>
    </div>

    <p class="text-xs text-center text-gray-400 dark:text-gray-500 mt-4">
        Los valores se guardan en <code>storage/app/config.json</code> dentro del servidor.
        Nunca se envían fuera de tu instalación.
    </p>

</div>
@endsection