<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Página no encontrada · UGR Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script>if (localStorage.getItem('tema') === 'oscuro') { document.documentElement.classList.add('dark'); }</script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center font-sans antialiased transition-colors duration-200">

    <div class="text-center px-6">

        {{-- Número de error --}}
        <div class="relative inline-block mb-8">
            <span class="text-[10rem] font-black text-gray-100 dark:text-gray-800 leading-none select-none">404</span>
            <div class="absolute inset-0 flex items-center justify-center">
                <svg class="w-24 h-24 text-teal-400 dark:text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Mensaje --}}
        <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white mb-3">
            Página no encontrada
        </h1>
        <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-8 leading-relaxed">
            El recurso que estás buscando no existe o ha sido movido.
            @if($exception->getMessage())
                <span class="block mt-2 text-xs font-mono text-gray-400 dark:text-gray-600">
                    {{ $exception->getMessage() }}
                </span>
            @endif
        </p>

        {{-- Acciones --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ url('/') }}"
                class="inline-flex items-center gap-2 bg-teal-700 hover:bg-teal-700 text-white font-bold py-3 px-6 rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Ir al Dashboard
            </a>
            <button onclick="history.back()"
                class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold py-3 px-6 rounded-xl border border-gray-200 dark:border-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver atrás
            </button>
        </div>

        {{-- Branding --}}
        <p class="mt-12 text-xs text-gray-300 dark:text-gray-700 font-medium tracking-widest uppercase">
            UGR Portfolio · TFG 2026
        </p>
    </div>

</body>
</html>