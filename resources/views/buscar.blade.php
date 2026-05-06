@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto">

    <div class="mb-4">
        <x-breadcrumb :links="[
            ['label' => 'Panel Global', 'url' => url('/')],
            ['label' => 'Búsqueda', 'url' => null],
        ]" />
    </div>

    {{-- BARRA DE BÚSQUEDA CON FILTRO DE ENTIDAD --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6"
         x-data="{ abierto: false }">
        <form method="GET" action="{{ route('buscar') }}" class="flex flex-col sm:flex-row gap-3">

            {{-- Campo de texto --}}
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="q" value="{{ $query }}"
                       placeholder="Buscar por nombre de proyecto, asunto o ID..."
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl
                              bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                              focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm transition">
            </div>

            {{-- Botón desplegable de Año --}}
            <div class="relative" x-data="{ abierto: false }">
                <button type="button" @click="abierto = !abierto"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-semibold transition whitespace-nowrap
                               {{ $selectedAnio
                                   ? 'bg-teal-50 border-teal-300 text-teal-800 dark:bg-teal-900/30 dark:border-teal-600 dark:text-teal-300'
                                   : 'bg-gray-50 border-gray-200 text-gray-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $selectedAnio ?: 'Año' }}
                    <svg class="w-3 h-3 transition-transform" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Dropdown --}}
                <div x-show="abierto" @click.outside="abierto = false" x-transition
                     class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                            rounded-xl shadow-lg z-50 overflow-hidden">

                    <a href="{{ route('buscar', array_filter(['q' => $query, 'entidad' => $selectedEntidad])) }}"
                       class="flex items-center gap-2 px-4 py-3 text-sm transition hover:bg-gray-50 dark:hover:bg-gray-700
                              {{ !$selectedAnio ? 'font-bold text-teal-700 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/20' : 'text-gray-700 dark:text-gray-300' }}">
                        <span class="w-2 h-2 rounded-full {{ !$selectedAnio ? 'bg-teal-500' : 'bg-transparent' }}"></span>
                        Todos los años
                    </a>

                    <div class="border-t border-gray-100 dark:border-gray-700 max-h-48 overflow-y-auto">
                        @foreach($anios as $anio)
                            <a href="{{ route('buscar', array_filter(['q' => $query, 'entidad' => $selectedEntidad, 'anio' => $anio])) }}"
                               class="flex items-center gap-2 px-4 py-2.5 text-sm transition hover:bg-teal-50 dark:hover:bg-teal-900/20
                                      {{ $selectedAnio === $anio
                                          ? 'font-bold text-teal-800 dark:text-teal-300 bg-teal-50 dark:bg-teal-900/20'
                                          : 'text-gray-700 dark:text-gray-300' }}">
                                <span class="w-2 h-2 rounded-full {{ $selectedAnio === $anio ? 'bg-teal-500' : 'bg-transparent' }}"></span>
                                {{ $anio }}
                            </a>
                        @endforeach
                    </div>
                </div>

                @if($selectedAnio)
                    <input type="hidden" name="anio" value="{{ $selectedAnio }}">
                @endif
            </div>

            {{-- Botón desplegable de Entidad Solicitante --}}
            <div class="relative" x-data="{ abierto: false }">
                <button type="button" @click="abierto = !abierto"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-semibold transition whitespace-nowrap
                               {{ $selectedEntidad
                                   ? 'bg-amber-50 border-amber-300 text-amber-800 dark:bg-amber-900/30 dark:border-amber-600 dark:text-amber-300'
                                   : 'bg-gray-50 border-gray-200 text-gray-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    {{ $selectedEntidad ?: 'Entidad Solicitante' }}
                    <svg class="w-3 h-3 transition-transform" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Dropdown --}}
                <div x-show="abierto" @click.outside="abierto = false" x-transition
                     class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                            rounded-xl shadow-lg z-50 overflow-hidden">

                    {{-- Opción: todas las entidades --}}
                    <a href="{{ route('buscar', array_filter(['q' => $query])) }}"
                       class="flex items-center gap-2 px-4 py-3 text-sm transition hover:bg-gray-50 dark:hover:bg-gray-700
                              {{ !$selectedEntidad ? 'font-bold text-teal-700 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/20' : 'text-gray-700 dark:text-gray-300' }}">
                        <span class="w-2 h-2 rounded-full {{ !$selectedEntidad ? 'bg-teal-500' : 'bg-transparent' }}"></span>
                        Todas las entidades
                    </a>

                    <div class="border-t border-gray-100 dark:border-gray-700 max-h-64 overflow-y-auto">
                        @foreach($entidades as $entidad)
                            <a href="{{ route('buscar', array_filter(['q' => $query, 'entidad' => $entidad])) }}"
                               class="flex items-center gap-2 px-4 py-2.5 text-sm transition hover:bg-amber-50 dark:hover:bg-amber-900/20
                                      {{ $selectedEntidad === $entidad
                                          ? 'font-bold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20'
                                          : 'text-gray-700 dark:text-gray-300' }}">
                                <span class="w-2 h-2 rounded-full {{ $selectedEntidad === $entidad ? 'bg-amber-500' : 'bg-transparent' }}"></span>
                                {{ $entidad }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Campo oculto para conservar la entidad al buscar --}}
                @if($selectedEntidad)
                    <input type="hidden" name="entidad" value="{{ $selectedEntidad }}">
                @endif
            </div>

            {{-- Botón buscar --}}
            <button type="submit"
                    class="px-5 py-2.5 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-xl text-sm transition shadow-sm">
                Buscar
            </button>

        </form>

        {{-- Filtros activos --}}
        @if($query || $selectedEntidad || $selectedAnio)
        <div class="mt-3 flex flex-wrap gap-2 items-center">
            <span class="text-xs text-gray-400 dark:text-gray-500">Filtrando por:</span>

            @if($query)
            <span class="inline-flex items-center gap-1 text-xs bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300
                         border border-teal-200 dark:border-teal-700 px-2.5 py-1 rounded-full font-semibold">
                "{{ $query }}"
                <a href="{{ route('buscar', array_filter(['entidad' => $selectedEntidad, 'anio' => $selectedAnio])) }}"
                   aria-label="Quitar filtro de texto" class="hover:text-red-500 transition ml-0.5">✕</a>
            </span>
            @endif

            @if($selectedEntidad)
            <span class="inline-flex items-center gap-1 text-xs bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300
                         border border-amber-200 dark:border-amber-600 px-2.5 py-1 rounded-full font-semibold">
                {{ $selectedEntidad }}
                <a href="{{ route('buscar', array_filter(['q' => $query, 'anio' => $selectedAnio])) }}"
                   aria-label="Quitar filtro de entidad" class="hover:text-red-500 transition ml-0.5">✕</a>
            </span>
            @endif

            @if($selectedAnio)
            <span class="inline-flex items-center gap-1 text-xs bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300
                         border border-teal-200 dark:border-teal-700 px-2.5 py-1 rounded-full font-semibold">
                {{ $selectedAnio }}
                <a href="{{ route('buscar', array_filter(['q' => $query, 'entidad' => $selectedEntidad])) }}"
                   aria-label="Quitar filtro de año" class="hover:text-red-500 transition ml-0.5">✕</a>
            </span>
            @endif

            <a href="{{ route('buscar') }}" class="text-xs text-gray-400 hover:text-red-500 transition">
                Limpiar todo
            </a>
        </div>
        @endif
    </div>

    {{-- RESULTADOS --}}
    @if(!$query && !$selectedEntidad && !$selectedAnio)
        <div class="bg-teal-50 dark:bg-teal-900/20 text-teal-800 dark:text-teal-300 p-6 rounded-xl
                    border border-teal-200 dark:border-teal-700 text-center">
            <p class="font-bold text-lg">Usa el buscador o filtra por entidad solicitante.</p>
            <p class="text-sm mt-1">Puedes combinar ambos filtros para refinar los resultados.</p>
        </div>
    @else
        <div class="mb-5 text-sm text-gray-500 dark:text-gray-400">
            Se encontraron
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ count($resultadosProyectos) }}</span>
            proyecto{{ count($resultadosProyectos) !== 1 ? 's' : '' }} y
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ count($resultadosTareas) }}</span>
            tarea{{ count($resultadosTareas) !== 1 ? 's' : '' }}.
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- PROYECTOS --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        Proyectos ({{ count($resultadosProyectos) }})
                    </h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($resultadosProyectos as $proyecto)
                        <a href="{{ route('proyectos.tareas', $proyecto['id']) }}"
                           class="block p-4 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition">
                            <span class="text-xs font-mono text-gray-400 dark:text-gray-500 block mb-1">ID: #{{ $proyecto['id'] }}</span>
                            <span class="font-semibold text-teal-700 dark:text-teal-400">{{ $proyecto['name'] }}</span>
                        </a>
                    @empty
                        <div class="p-6 text-center text-gray-400 dark:text-gray-500 text-sm italic">
                            No hay coincidencias en proyectos.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- TAREAS --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Tareas ({{ count($resultadosTareas) }})
                    </h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($resultadosTareas as $tarea)
                        <a href="{{ route('tareas.show', $tarea['id']) }}"
                           class="block p-4 hover:bg-amber-50 dark:hover:bg-amber-900/10 transition">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-xs font-mono text-gray-400 dark:text-gray-500">
                                    #{{ $tarea['id'] }} · {{ $tarea['_links']['status']['title'] ?? '' }}
                                </span>
                                @if(!empty($tarea['customField3']))
                                    <span class="text-xs bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300
                                                 border border-amber-200 dark:border-amber-700 px-2 py-0.5 rounded-full
                                                 font-semibold shrink-0">
                                        {{ $tarea['customField3'] }}
                                    </span>
                                @endif
                            </div>
                            <span class="font-semibold text-gray-800 dark:text-gray-200 text-sm">
                                {{ $tarea['subject'] }}
                            </span>
                            @if(!empty($tarea['customField3']) && !$selectedEntidad)
                                <div class="mt-1.5">
                                    <a href="{{ route('buscar', array_filter(['q' => $query, 'entidad' => $tarea['customField3']])) }}"
                                       class="text-xs text-amber-600 dark:text-amber-400 hover:underline"
                                       onclick="event.stopPropagation()">
                                        Ver solo esta entidad →
                                    </a>
                                </div>
                            @endif
                        </a>
                    @empty
                        <div class="p-6 text-center text-gray-400 dark:text-gray-500 text-sm italic">
                            No hay coincidencias en tareas.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    @endif

</div>
@endsection