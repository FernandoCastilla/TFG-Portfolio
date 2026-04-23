@extends('layouts.app')
@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="mb-4">
            <x-breadcrumb :links="[
            ['label' => 'Panel Global', 'url' => url('/')],
            ['label' => 'Resultados de Búsqueda', 'url' => null],
        ]" />
        </div>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
                Resultados para: <span class="text-teal-700 dark:text-teal-400">"{{ $query }}"</span>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                Se han encontrado {{ count($resultadosProyectos) }} proyectos y {{ count($resultadosTareas) }} tareas.
            </p>
        </div>

        @if(empty($query))
            <div
                class="bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-300 p-6 rounded-xl border border-yellow-200 dark:border-yellow-700 text-center">
                <p class="font-bold text-lg">No has escrito nada en el buscador.</p>
                <p class="text-sm">Escribe un ID, el nombre de un proyecto o el asunto de una tarea en el menú lateral.</p>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            Proyectos Encontrados ({{ count($resultadosProyectos) }})
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($resultadosProyectos as $proyecto)
                            <a href="{{ route('proyectos.tareas', $proyecto['id']) }}"
                                class="block p-4 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition">
                                <span class="text-xs font-mono text-gray-400 dark:text-gray-500 block mb-1">ID:
                                    #{{ $proyecto['id'] }}</span>
                                <span class="font-semibold text-teal-700 dark:text-teal-400">{{ $proyecto['name'] }}</span>
                            </a>
                        @empty
                            <div class="p-6 text-center text-gray-500 dark:text-gray-400 text-sm">No hay coincidencias en proyectos.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                </path>
                            </svg>
                            Tareas Encontradas ({{ count($resultadosTareas) }})
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($resultadosTareas as $tarea)
                            @php
                                $hrefProyecto = $tarea['_links']['project']['href'] ?? '';
                                $proyectoId = last(explode('/', $hrefProyecto));
                            @endphp
                            <a href="{{ route('tareas.show', $tarea['id']) }}"
                                class="block p-4 hover:bg-orange-50 dark:hover:bg-orange-900/10 transition">
                                <span class="text-xs font-mono text-gray-400 dark:text-gray-500 block mb-1">
                                    ID: #{{ $tarea['id'] }} | {{ $tarea['_links']['status']['title'] ?? '' }}
                                </span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $tarea['subject'] }}</span>
                            </a>
                        @empty
                            <div class="p-6 text-center text-gray-500 dark:text-gray-400 text-sm">No hay coincidencias en tareas.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        @endif
    </div>
@endsection