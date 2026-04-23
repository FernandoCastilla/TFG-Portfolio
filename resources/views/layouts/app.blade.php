<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TFG Dashboard - Fernando</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- Aplicar tema ANTES de renderizar para evitar parpadeo --}}
    <script>if (localStorage.getItem('tema') === 'oscuro') { document.documentElement.classList.add('dark'); }</script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 font-sans antialiased text-gray-900 dark:text-gray-100 transition-colors duration-200">

    <aside class="fixed top-0 left-0 w-64 h-screen bg-teal-900 text-white flex flex-col shadow-2xl z-20 border-r border-teal-950 dark:bg-gray-900 dark:border-gray-800">
        {{-- Cabecera institucional — inspirada en el estilo visual del Vicerrectorado de Transformación Digital UGR --}}
        <div class="flex flex-col items-center justify-center border-b border-teal-800 dark:border-gray-800 bg-teal-950 dark:bg-gray-950 px-4 py-3 gap-1">
            <span class="text-[10px] font-semibold tracking-widest uppercase text-teal-300 dark:text-teal-400 leading-tight">
                Universidad de Granada
            </span>
            <h1 class="text-sm font-bold tracking-wide text-white leading-tight text-center">
                Vicerrectorado de<br>Transformación Digital
            </h1>
            <div class="mt-1 px-3 py-0.5 bg-teal-700 dark:bg-teal-800 rounded-full">
                <span class="text-[10px] font-bold tracking-widest uppercase text-teal-100">UGR Portfolio</span>
            </div>
        </div>

        <div class="px-4 py-4 border-b border-teal-800 dark:border-gray-800 bg-teal-900 dark:bg-gray-900">
            <form action="{{ route('buscar') }}" method="GET" class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-teal-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar proyecto o tarea..."
                       class="w-full bg-teal-800 dark:bg-gray-800 text-sm text-white dark:text-gray-200 border border-teal-700 dark:border-gray-700 rounded-lg pl-9 pr-3 py-2 focus:ring-teal-500 focus:border-teal-500 placeholder-teal-400 dark:placeholder-gray-500 transition-colors">
            </form>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto bg-teal-900 dark:bg-gray-900">
            <a href="{{ url('/') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->is('/') ? 'bg-teal-700 dark:bg-teal-700 text-white' : 'text-teal-100 dark:text-gray-400 hover:bg-teal-800 dark:hover:bg-gray-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                Panel Global
            </a>

            @php
                $proyectosSidebar = [];
                $rutaSidebarJson = storage_path('app/proyectos_ugr.json');
                if (\Illuminate\Support\Facades\File::exists($rutaSidebarJson)) {
                    $datosSidebar = json_decode(\Illuminate\Support\Facades\File::get($rutaSidebarJson), true);
                    $proyectosSidebar = $datosSidebar['_embedded']['elements'] ?? [];
                }
                $proyectosAbierto = request()->is('proyectos*');
            @endphp

            <div x-data="{ abierto: {{ $proyectosAbierto ? 'true' : 'false' }} }">
                <button @click="abierto = !abierto"
                    class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg transition-all {{ $proyectosAbierto ? 'bg-teal-700 dark:bg-teal-700 text-white' : 'text-teal-100 dark:text-gray-400 hover:bg-teal-800 dark:hover:bg-gray-800' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        <span>Proyectos UGR</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="abierto" x-transition class="mt-1 ml-3 border-l border-teal-700 dark:border-gray-700 pl-3 space-y-1">
                    @forelse($proyectosSidebar as $proy)
                        <a href="{{ route('proyectos.tareas', $proy['id']) }}"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all truncate
                                {{ request()->is('proyectos/' . $proy['id']) || request()->is('proyectos/' . $proy['id'] . '/tareas')
                                    ? 'bg-teal-600 dark:bg-teal-600 text-white'
                                    : 'text-teal-200 dark:text-gray-400 hover:bg-teal-800 dark:hover:bg-gray-800 hover:text-white dark:hover:text-gray-200' }}">
                            <span class="text-xs text-teal-400 dark:text-gray-500 flex-shrink-0">#{{ $proy['id'] }}</span>
                            <span class="truncate">{{ $proy['name'] }}</span>
                        </a>
                    @empty
                        <p class="px-3 py-2 text-xs text-teal-400 dark:text-gray-500 italic">Sin proyectos cargados</p>
                    @endforelse
                </div>
            </div>

            <a href="{{ route('calendario') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('calendario') ? 'bg-teal-700 dark:bg-teal-700 text-white' : 'text-teal-100 dark:text-gray-400 hover:bg-teal-800 dark:hover:bg-gray-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Calendario
            </a>
        </nav>

        {{-- PIE: toggle tema + usuario --}}
        <div class="p-4 border-t border-teal-800 dark:border-gray-800 bg-teal-950 dark:bg-gray-950 space-y-3">

            {{-- Toggle modo oscuro --}}
            <button onclick="toggleTema()" id="btn-tema"
                aria-label="Activar modo oscuro"
                class="w-full flex items-center justify-between px-3 py-2 rounded-lg bg-teal-800 dark:bg-gray-800 hover:bg-teal-700 dark:hover:bg-gray-700 transition-colors text-sm text-teal-100 dark:text-gray-300">
                <span class="flex items-center gap-2">
                    <svg id="icono-luna" class="w-4 h-4 text-teal-300 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg id="icono-sol" class="w-4 h-4 text-yellow-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                    </svg>
                    <span id="texto-tema">Modo oscuro</span>
                </span>
                <div id="pill-tema" class="w-10 h-5 bg-gray-600 rounded-full relative transition-colors duration-300">
                    <div id="circulo-tema" class="absolute top-0.5 left-0.5 w-4 h-4 bg-gray-400 rounded-full transition-all duration-300"></div>
                </div>
            </button>

            @auth
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 flex-shrink-0 rounded-full bg-teal-600 dark:bg-teal-600 flex items-center justify-center text-sm font-bold text-white uppercase">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="text-sm truncate">
                            <p class="font-medium text-gray-200 truncate">{{ auth()->user()->name }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0 ml-2">
                        @csrf
                        <button type="submit" aria-label="Cerrar sesión" class="text-gray-400 hover:text-red-400 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    Iniciar Sesión
                </a>
            @endauth
        </div>
    </aside>

    <div class="ml-64 flex flex-col min-h-screen">
        <main class="flex-1 p-8 bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
            @yield('content')
        </main>

        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-8 py-4 flex items-center justify-between mt-auto transition-colors duration-200">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                © 2026 UGR Portfolio. Trabajo de Fin de Grado - Fernando Castilla
            </p>

            @php
                $estadoConexion = \Illuminate\Support\Facades\Cache::get('api_conectada', false);
            @endphp

            @if($estadoConexion)
                <div class="flex items-center gap-2 text-xs text-green-700 bg-green-50 px-3 py-1.5 rounded-full border border-green-200 font-medium shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    API Conectada: proyectostic.ugr.es
                </div>
            @else
                <div class="flex items-center gap-2 text-xs text-red-700 bg-red-50 px-3 py-1.5 rounded-full border border-red-200 font-medium shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                    API Desconectada (Requiere Sincronización)
                </div>
            @endif
        </footer>
    </div>

    <script>
        //  Sincronizar UI del toggle con el estado actual al cargar
        (function () {
            aplicarEstadoToggle(localStorage.getItem('tema') === 'oscuro');
        })();

        function toggleTema() {
            const oscuro = document.documentElement.classList.toggle('dark');
            localStorage.setItem('tema', oscuro ? 'oscuro' : 'claro');
            aplicarEstadoToggle(oscuro);
        }

        function aplicarEstadoToggle(oscuro) {
            const luna    = document.getElementById('icono-luna');
            const sol     = document.getElementById('icono-sol');
            const texto   = document.getElementById('texto-tema');
            const pill    = document.getElementById('pill-tema');
            const circulo = document.getElementById('circulo-tema');
            if (!luna) return;

            if (oscuro) {
                luna.classList.add('hidden');
                sol.classList.remove('hidden');
                texto.textContent = 'Modo claro';
                pill.classList.remove('bg-gray-600');
                pill.classList.add('bg-teal-600');
                circulo.classList.add('translate-x-5', 'bg-white');
                circulo.classList.remove('bg-gray-400');
                document.getElementById('btn-tema').setAttribute('aria-label', 'Activar modo claro');
            } else {
                sol.classList.add('hidden');
                luna.classList.remove('hidden');
                texto.textContent = 'Modo oscuro';
                pill.classList.remove('bg-teal-600');
                pill.classList.add('bg-gray-600');
                circulo.classList.remove('translate-x-5', 'bg-white');
                circulo.classList.add('bg-gray-400');
                document.getElementById('btn-tema').setAttribute('aria-label', 'Activar modo oscuro');
            }
        }
    </script>

</body>
</html>