<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Error del servidor · UGR Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script>if (localStorage.getItem('tema') === 'oscuro') { document.documentElement.classList.add('dark'); }</script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center font-sans antialiased transition-colors duration-200">

    <div class="text-center px-6">

        {{-- Número de error --}}
        <div class="relative inline-block mb-8">
            <span class="text-[10rem] font-black text-gray-100 dark:text-gray-800 leading-none select-none">500</span>
            <div class="absolute inset-0 flex items-center justify-center">
                <svg class="w-24 h-24 text-red-400 dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
        </div>

        {{-- Mensaje --}}
        <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white mb-3">
            Error interno del servidor
        </h1>
        <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-8 leading-relaxed">
            Algo ha fallado en el servidor. Si el problema persiste, prueba a
            sincronizar de nuevo los datos desde el dashboard.
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
            <button onclick="location.reload()"
                class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold py-3 px-6 rounded-xl border border-gray-200 dark:border-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reintentar
            </button>
        </div>

        {{-- Branding --}}
        <p class="mt-12 text-xs text-gray-300 dark:text-gray-700 font-medium tracking-widest uppercase">
            UGR Portfolio · TFG 2026
        </p>
    </div>

</body>
</html>