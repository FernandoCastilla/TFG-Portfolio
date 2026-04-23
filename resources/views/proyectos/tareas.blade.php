@extends('layouts.app')

@section('content')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Paleta de colores dinámica basada en los estados únicos de las tareas --}}
    @php
        $estadosUnicos = collect($tareas->items())->map(function ($tarea) {
            return $tarea['_links']['status']['title'] ?? 'Desconocido';
        })->unique()->values()->toArray();

        $coloresBase = [
            '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
            '#06B6D4', '#EC4899', '#F97316', '#14B8A6', '#64748B'
        ];

        $paletaEstados = [];
        foreach ($estadosUnicos as $index => $estado) {
            $paletaEstados[$estado] = $coloresBase[$index % count($coloresBase)];
        }

        $coloresGrafica = [];
        foreach ($graficaLabels as $label) {
            $coloresGrafica[] = $paletaEstados[$label] ?? '#64748B';
        }
    @endphp

    <style>
        .op-content p { margin-bottom: 0.75rem; }
        .op-content table { width: 100%; border-collapse: collapse; margin-top: 1rem; margin-bottom: 1rem; background-color: white; }
        .op-content td, .op-content th { border: 1px solid #d1d5db; padding: 0.75rem; vertical-align: top; }
        .op-content ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 0.75rem; }
        .op-content figure { margin: 0; }
        
        /* Pequeña animación para la flecha de desplegar */
        .btn-desplegar { transition: transform 0.2s ease-in-out; }
        .btn-desplegar.rotado { transform: rotate(90deg); }
    </style>

    <div class="max-w-6xl mx-auto">

        {{-- CABECERA DEL PROYECTO --}}
        <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <x-breadcrumb :links="[
                ['label' => 'Panel Global', 'url' => url('/')],
                ['label' => 'Proyectos UGR', 'url' => route('proyectos.index')],
                ['label' => $proyecto['name']]
            ]" />

            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ $proyecto["name"] }}</h1>
                    <div class="flex gap-2 mt-3">
                        @if($proyecto['active'])
                            <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full">Activo</span>
                        @else
                            <span class="bg-red-100 text-red-800 text-xs font-bold px-3 py-1 rounded-full">Inactivo</span>
                        @endif
                        @if($proyecto['public'])
                            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full">Público</span>
                        @else
                            <span class="bg-gray-100 text-gray-800 text-xs font-bold px-3 py-1 rounded-full">Privado</span>
                        @endif
                        @if(isset($proyecto['_links']['status']['title']))
                            <span class="bg-purple-100 text-purple-800 text-xs font-bold px-3 py-1 rounded-full">
                                {{ $proyecto['_links']['status']['title'] }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-gray-400 font-mono text-sm">ID: #{{ $proyecto['id'] }}</span>
                    <a href="{{ route('proyectos.pdf', $proyecto['id']) }}" target="_blank"
                        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Exportar PDF
                    </a>
                    <a href="{{ route('proyectos.csv', $proyecto['id']) }}"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                            </path>
                        </svg>
                        Exportar CSV
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-gray-100">
                <div>
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Descripción</h3>
                    <div class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600 op-content text-sm">
                        @if(!empty($proyecto['description']['html']))
                            {!! $proyecto['description']['html'] !!}
                        @else
                            <p class="text-gray-400 italic mb-0">No hay descripción disponible.</p>
                        @endif
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Estado detallado</h3>
                    <div class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600 op-content text-sm">
                        @if(!empty($proyecto['statusExplanation']['html']))
                            {!! $proyecto['statusExplanation']['html'] !!}
                        @else
                            <p class="text-gray-400 italic mb-0">No hay explicación de estado detallada.</p>
                        @endif
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Información Técnica</h3>
                    <ul class="text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600 space-y-3 text-sm">
                        <li class="flex justify-between border-b border-gray-200 dark:border-gray-600 pb-2">
                            <strong>Identificador:</strong>
                            <span class="font-mono text-teal-700">{{ $proyecto['identifier'] }}</span>
                        </li>
                        <li class="flex justify-between border-b border-gray-200 dark:border-gray-600 pb-2">
                            <strong>Creado el:</strong>
                            <span>{{ date('d/m/Y H:i', strtotime($proyecto['createdAt'])) }}</span>
                        </li>
                        <li class="flex justify-between">
                            <strong>Última actualización:</strong>
                            <span>{{ date('d/m/Y H:i', strtotime($proyecto['updatedAt'])) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- TÍTULO SECCIÓN TAREAS --}}
        <div class="mb-4">
            <h2 class="text-2xl font-bold text-gray-800">
                Work Packages de: <span class="text-teal-700">{{ $proyecto['name'] }}</span>
            </h2>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6 relative z-20">

            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-teal-600 to-teal-400 rounded-t-xl"></div>

            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    Filtros de Búsqueda
                </h3>

                @if($selectedTipo || $selectedEstado || $selectedAsignado || request('year') || request('month') || $selectedEntidad)
                    <a href="{{ url()->current() }}"
                        class="text-sm text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold transition bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg border border-red-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                        Limpiar Filtros
                    </a>
                @endif
            </div>

            <form action="{{ url()->current() }}" method="GET"
                class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-8 gap-5 items-end">

                <div class="col-span-1 md:col-span-3 xl:col-span-8 mb-2">
                    <label for="search" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Buscar por palabra clave</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            class="bg-gray-50 border border-gray-200 text-gray-800 text-base rounded-xl focus:ring-teal-500 focus:border-teal-600 block w-full pl-12 p-3.5 transition-colors shadow-sm focus:bg-white"
                            placeholder="Ej: Servidor, API, Base de datos, Diseño...">
                        
                        @if(request('search'))
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <button type="button" aria-label="Limpiar búsqueda" onclick="document.getElementById('search').value=''; this.form.submit();" class="text-gray-400 hover:text-red-500 transition-colors focus:outline-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                <div>
                    <label for="tipo" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tipo</label>
                    <div class="relative">
                        <select name="tipo" id="tipo"
                            class="appearance-none bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-600 block w-full px-4 py-2.5 transition-colors shadow-sm cursor-pointer hover:bg-white">
                            <option value="">Todos los tipos</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo }}" {{ $selectedTipo == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="estado" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Estado</label>
                    <div class="relative">
                        <select name="estado" id="estado"
                            class="appearance-none bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-600 block w-full px-4 py-2.5 transition-colors shadow-sm cursor-pointer hover:bg-white">
                            <option value="">Todos los estados</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado }}" {{ $selectedEstado == $estado ? 'selected' : '' }}>{{ $estado }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="asignado" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Asignado a</label>
                    <div class="relative">
                        <select name="asignado" id="asignado"
                            class="appearance-none bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-600 block w-full px-4 py-2.5 transition-colors shadow-sm cursor-pointer hover:bg-white">
                            <option value="">Cualquier persona</option>
                            @foreach($asignados as $asignado)
                                <option value="{{ $asignado }}" {{ $selectedAsignado == $asignado ? 'selected' : '' }}>
                                    {{ $asignado }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="prioridad" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Prioridad</label>
                    <div class="relative">
                        <select name="prioridad" id="prioridad"
                            class="appearance-none bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-600 block w-full px-4 py-2.5 transition-colors shadow-sm cursor-pointer hover:bg-white">
                            <option value="">Todas las prioridades</option>
                            @foreach($prioridades as $prioridad)
                                <option value="{{ $prioridad }}" {{ $selectedPrioridad == $prioridad ? 'selected' : '' }}>{{ $prioridad }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="entidad" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Entidad Solicitante</label>
                    <div class="relative">
                        <select name="entidad" id="entidad"
                            class="appearance-none bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-600 block w-full px-4 py-2.5 transition-colors shadow-sm cursor-pointer hover:bg-white">
                            <option value="">Todas las entidades</option>
                            @foreach($entidades as $entidad)
                                <option value="{{ $entidad }}" {{ $selectedEntidad == $entidad ? 'selected' : '' }}>{{ $entidad }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="relative">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Fecha (Inicio)</label>
                    <button type="button" onclick="toggleDateFilter(event)"
                        class="w-full bg-gray-50 border border-gray-200 hover:bg-white text-gray-800 text-sm py-2.5 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 flex justify-between items-center transition-colors shadow-sm">
                        <span class="flex items-center gap-2 truncate">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span id="date-display-text">
                                @if(request('year'))
                                    <span class="font-bold text-teal-700">{{ request('month') ? request('month') . '/' : '' }}{{ request('year') }}</span>
                                @else
                                    Cualquier fecha
                                @endif
                            </span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div id="date-popup" class="hidden absolute left-0 mt-2 w-64 rounded-xl shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-50 p-4 border border-gray-100">
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-gray-600 mb-1">Año</label>
                            <select id="filter-year" name="year" class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-teal-500 focus:border-teal-600">
                                <option value="">Todos los años</option>
                                <option value="2026" {{ request('year') == '2026' ? 'selected' : '' }}>2026</option>
                                <option value="2025" {{ request('year') == '2025' ? 'selected' : '' }}>2025</option>
                                <option value="2024" {{ request('year') == '2024' ? 'selected' : '' }}>2024</option>
                                <option value="2023" {{ request('year') == '2023' ? 'selected' : '' }}>2023</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-600 mb-1">Mes (Opcional)</label>
                            <select id="filter-month" name="month" class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-teal-500 focus:border-teal-600">
                                <option value="">Todos los meses</option>
                                <option value="01" {{ request('month') == '01' ? 'selected' : '' }}>01 - Enero</option>
                                <option value="02" {{ request('month') == '02' ? 'selected' : '' }}>02 - Febrero</option>
                                <option value="03" {{ request('month') == '03' ? 'selected' : '' }}>03 - Marzo</option>
                                <option value="04" {{ request('month') == '04' ? 'selected' : '' }}>04 - Abril</option>
                                <option value="05" {{ request('month') == '05' ? 'selected' : '' }}>05 - Mayo</option>
                                <option value="06" {{ request('month') == '06' ? 'selected' : '' }}>06 - Junio</option>
                                <option value="07" {{ request('month') == '07' ? 'selected' : '' }}>07 - Julio</option>
                                <option value="08" {{ request('month') == '08' ? 'selected' : '' }}>08 - Agosto</option>
                                <option value="09" {{ request('month') == '09' ? 'selected' : '' }}>09 - Septiembre</option>
                                <option value="10" {{ request('month') == '10' ? 'selected' : '' }}>10 - Octubre</option>
                                <option value="11" {{ request('month') == '11' ? 'selected' : '' }}>11 - Noviembre</option>
                                <option value="12" {{ request('month') == '12' ? 'selected' : '' }}>12 - Diciembre</option>
                            </select>
                        </div>
                        <button type="button" onclick="updateDateLabel(); toggleDateFilter()"
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-1.5 px-4 rounded-lg transition-colors text-sm">
                            Listo
                        </button>
                    </div>
                </div>
                <div>
                    <label for="sort" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Ordenar por</label>
                    <div class="relative">
                        <select name="sort" id="sort"
                            class="appearance-none bg-teal-50 border border-teal-200 text-teal-800 font-semibold text-sm rounded-lg focus:ring-teal-500 focus:border-teal-600 block w-full px-4 py-2.5 transition-colors shadow-sm cursor-pointer hover:bg-teal-50">
                            <option value="">Por defecto (Jerarquía)</option>
                            <option value="date_desc" {{ request('sort') == 'date_desc' ? 'selected' : '' }}>Fecha (Más recientes)</option>
                            <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>Fecha (Más antiguas)</option>
                            <option value="progress_desc" {{ request('sort') == 'progress_desc' ? 'selected' : '' }}>Progreso (100% a 0%)</option>
                            <option value="progress_asc" {{ request('sort') == 'progress_asc' ? 'selected' : '' }}>Progreso (0% a 100%)</option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nombre (A - Z)</option>
                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nombre (Z - A)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-teal-700 hover:bg-teal-700 text-white font-bold py-2.5 px-4 rounded-lg transition shadow-md border border-teal-800 focus:ring-4 focus:ring-teal-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>

        <div
            class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-8 flex flex-col md:flex-row items-center gap-8">
            <div class="w-full md:w-1/3 relative h-64">
                <canvas id="graficaEstados"></canvas>
            </div>

            <div class="w-full md:w-2/3">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Distribución de Tareas</h2>
                <p class="text-gray-600">
                    Este gráfico muestra el volumen de trabajo repartido según el estado de los Work Packages dentro de
                    OpenProject.
                </p>
                <div
                    class="mt-4 inline-block bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 font-bold px-4 py-2 rounded-lg border border-teal-100 dark:border-indigo-800">
                    Total de tareas: {{ $totalTareasFiltradas ?? $tareas->total() }}
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-600">
                            <th class="p-4 font-semibold">ID</th>
                            <th class="p-4 font-semibold">Asunto (Subject)</th>
                            <th class="p-4 font-semibold">Tipo</th>
                            <th class="p-4 font-semibold">Estado</th>
                            <th class="p-4 font-semibold w-32">Progreso</th>
                            <th class="p-4 font-semibold">Asignado a</th>
                            <th class="p-4 font-semibold">T. Estimado</th>
                            <th class="p-4 font-semibold">T. Dedicado</th>
                            <th class="p-4 font-semibold">Entidad Solicitante</th>
                            <th class="p-4 font-semibold">Fecha Inicio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tareas as $tarea)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition border-b border-gray-100 dark:border-gray-700 fila-tarea" 
                                data-id="{{ $tarea['id'] }}" 
                                data-parent="{{ $tarea['padre_id'] ?? '' }}"
                                style="{{ ($tarea['nivel'] ?? 0) > 0 ? 'display: none;' : '' }}">
                                
                                <td class="p-4 text-gray-500 dark:text-gray-400 font-mono text-sm">#{{ $tarea['id'] }}</td>
                                
                                <td class="p-4 font-semibold text-gray-800 dark:text-gray-100">
                                    <div class="flex items-start gap-1" style="padding-left: {{ ($tarea['nivel'] ?? 0) * 1.5 }}rem;">
                                        
                                        {{-- Si tiene hijos, mostramos el botón desplegable --}}
                                        @if($tarea['tiene_hijos'])
                                            <button type="button"
                                                aria-label="Desplegar subtareas de {{ $tarea['subject'] }}"
                                                aria-expanded="false"
                                                onclick="toggleHijos({{ $tarea['id'] }}, this)"
                                                class="btn-desplegar mt-0.5 text-gray-400 hover:text-teal-700 focus:outline-none shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </button>
                                        {{-- Si es un hijo final (sin hijos propios), mostramos la flechita "L" --}}
                                        @elseif(($tarea['nivel'] ?? 0) > 0)
                                            <svg class="w-4 h-4 text-teal-300 shrink-0 mt-1 ml-1 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5v8a2 2 0 002 2h8M15 11l4 4-4 4"></path>
                                            </svg>
                                        {{-- Si es padre huérfano (sin hijos), metemos un espacio invisible para mantener la alineación --}}
                                        @else
                                            <div class="w-5 h-5 mr-1"></div>
                                        @endif

                                        <div class="flex flex-col">
                                            <a href="{{ route('tareas.show', $tarea['id']) }}"
                                                class="hover:text-teal-700 hover:underline transition-colors leading-tight">
                                                {{ $tarea['subject'] }}
                                            </a>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-4">
                                    <span class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs px-2 py-1 rounded border border-gray-200 dark:border-gray-600">
                                        {{ $tarea['_links']['type']['title'] ?? 'Desconocido' }}
                                    </span>
                                </td>

                                <td class="p-4">
                                    @php
                                        $estado = $tarea['_links']['status']['title'] ?? 'N/A';
                                        $colorHex = $paletaEstados[$estado] ?? '#64748B';
                                    @endphp
                                    <span class="text-xs font-bold px-3 py-1 rounded-full border"
                                        style="background-color: {{ $colorHex }}1A; color: {{ $colorHex }}; border-color: {{ $colorHex }}40;">
                                        {{ $estado }}
                                    </span>
                                </td>

                                <td class="p-4">
                                    @php
                                        $porcentaje = $tarea['percentageDone'] ?? 0;
                                        $colorBarra = $porcentaje > 0 ? $colorHex : '#D1D5DB'; 
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 border border-gray-300">
                                            <div class="h-2.5 rounded-full transition-all duration-500"
                                                style="width: {{ $porcentaje }}%; background-color: {{ $colorBarra }};"></div>
                                        </div>
                                        <span class="text-xs font-bold text-gray-700 w-8 text-right">{{ $porcentaje }}%</span>
                                    </div>
                                </td>

                                <td class="p-4">
                                    @php
                                        $asignado = $tarea['_links']['assignee']['title'] ?? null;
                                    @endphp

                                    @if($asignado)
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-7 h-7 rounded-full bg-teal-50 text-teal-700 flex items-center justify-center text-xs font-bold border border-teal-200">
                                                {{ strtoupper(substr($asignado, 0, 1)) }}
                                            </div>
                                            <span class="text-sm font-medium text-gray-700">{{ $asignado }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Sin asignar</span>
                                    @endif
                                </td>

                                @php
                                    $estimado = \App\Http\Controllers\ProjectController::parsearDuracion($tarea['estimatedTime'] ?? null);
                                    $dedicado = \App\Http\Controllers\ProjectController::parsearDuracion($tarea['spentTime'] ?? null);
                                @endphp

                                <td class="p-4 text-gray-600 dark:text-gray-400 text-sm font-mono">
                                    {{ $estimado ?? '—' }}
                                </td>

                                <td class="p-4 text-sm font-mono">
                                    @if($dedicado)
                                        @php
                                            $superado = $estimado && $dedicado &&
                                                preg_match('/(\d+)h/', $dedicado, $md) &&
                                                preg_match('/(\d+)h/', $estimado, $me) &&
                                                (int)$md[1] > (int)$me[1];
                                        @endphp
                                        <span class="{{ $superado ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-gray-600 dark:text-gray-400' }}">
                                            {{ $dedicado }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-600">—</span>
                                    @endif
                                </td>

                                <td class="p-4">
                                    @php $entidad = $tarea['customField3'] ?? null; @endphp
                                    @if($entidad)
                                        <div class="flex flex-col gap-0.5">
                                            <span class="inline-block bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold px-2 py-1 rounded-lg">
                                                {{ $entidad }}
                                            </span>
                                            @if(!empty($tarea['customField4']))
                                                <span class="text-xs text-gray-500">
                                                    {{ $tarea['customField4'] }}
                                                    @if(!empty($tarea['customField5']))
                                                        · <a href="mailto:{{ $tarea['customField5'] }}" class="text-teal-600 hover:underline">{{ $tarea['customField5'] }}</a>
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">—</span>
                                    @endif
                                </td>

                                <td class="p-4 text-gray-600 dark:text-gray-400 text-sm">
                                    {{ !empty($tarea['startDate']) ? date('d/m/Y', strtotime($tarea['startDate'])) : '-' }}
                                </td>
                                
                            </tr> @empty
                            <tr>
                                <td colspan="10" class="p-6 text-center text-gray-500 text-lg">
                                    No se encontraron tareas con estos filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($tareas->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-3">

                    {{-- Información de página --}}
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Mostrando
                        <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $tareas->firstItem() }}–{{ $tareas->lastItem() }}</span>
                        de
                        <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $totalTareasFiltradas ?? $tareas->total() }}</span>
                        tareas
                    </p>

                    {{-- Botones de navegación --}}
                    <nav aria-label="Navegación de páginas" class="flex items-center gap-1">
                        {{-- Anterior --}}
                        @if($tareas->onFirstPage())
                            <span aria-disabled="true" aria-label="Página anterior (no disponible)"
                                class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg cursor-not-allowed">
                                &lsaquo;
                            </span>
                        @else
                            <a href="{{ $tareas->previousPageUrl() }}" aria-label="Página anterior"
                               class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                &lsaquo;
                            </a>
                        @endif

                        {{-- Números de página --}}
                        @foreach($tareas->getUrlRange(1, $tareas->lastPage()) as $page => $url)
                            @if($page == $tareas->currentPage())
                                <span aria-current="page" aria-label="Página {{ $page }}, página actual"
                                    class="px-3 py-1.5 text-sm font-bold text-white bg-teal-700 border border-teal-700 rounded-lg">
                                    {{ $page }}
                                </span>
                            @elseif(abs($page - $tareas->currentPage()) <= 2 || $page == 1 || $page == $tareas->lastPage())
                                <a href="{{ $url }}" aria-label="Página {{ $page }}"
                                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    {{ $page }}
                                </a>
                            @elseif(abs($page - $tareas->currentPage()) == 3)
                                <span aria-hidden="true" class="px-2 py-1.5 text-sm text-gray-400 dark:text-gray-600">…</span>
                            @endif
                        @endforeach

                        {{-- Siguiente --}}
                        @if($tareas->hasMorePages())
                            <a href="{{ $tareas->nextPageUrl() }}" aria-label="Página siguiente"
                               class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                &rsaquo;
                            </a>
                        @else
                            <span aria-disabled="true" aria-label="Página siguiente (no disponible)"
                                class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg cursor-not-allowed">
                                &rsaquo;
                            </span>
                        @endif
                    </nav>

                </div>
            @endif

        </div>

    </div>

    <script>
        // Lógica de Gráfica y Popups de fecha (sin cambios)
        const labels = {!! json_encode($graficaLabels) !!};
        const datos = {!! json_encode($graficaDatos) !!};
        const backgroundColors = {!! json_encode($coloresGrafica) !!};

        if (datos.length > 0) {
            const ctx = document.getElementById('graficaEstados').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: datos,
                        backgroundColor: backgroundColors,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { padding: 20, font: { family: 'sans-serif' } }
                        }
                    },
                    cutout: '65%'
                }
            });
        } else {
            document.getElementById('graficaEstados').style.display = 'none';
        }

        function toggleDateFilter(event) {
            if (event) event.stopPropagation();
            const popup = document.getElementById('date-popup');
            popup.classList.toggle('hidden');
        }

        function updateDateLabel() {
            const year = document.getElementById('filter-year').value;
            const month = document.getElementById('filter-month').value;
            const displaySpan = document.getElementById('date-display-text');

            if (year) {
                const text = month ? `${month}/${year}` : year;
                displaySpan.innerHTML = `<span class="font-bold text-teal-700">${text}</span>`;
            } else {
                displaySpan.innerHTML = 'Cualquier fecha';
            }
        }

        document.addEventListener('click', function (event) {
            const popup = document.getElementById('date-popup');
            if (!popup.classList.contains('hidden')) {
                const button = popup.previousElementSibling;
                if (!popup.contains(event.target) && !button.contains(event.target)) {
                    popup.classList.add('hidden');
                }
            }
        });

        // --- NUEVA LÓGICA PARA DESPLEGAR TAREAS HIJAS ---
        function toggleHijos(tareaId, boton) {
            boton.classList.toggle('rotado');
            const estaDesplegando = boton.classList.contains('rotado');

            // Actualizar aria-expanded para accesibilidad
            boton.setAttribute('aria-expanded', estaDesplegando ? 'true' : 'false');

            const hijos = document.querySelectorAll(`.fila-tarea[data-parent="${tareaId}"]`);

            hijos.forEach(hijo => {
                if (estaDesplegando) {
                    hijo.style.display = 'table-row';
                } else {
                    hijo.style.display = 'none';
                    ocultarDescendencia(hijo.getAttribute('data-id'));
                    const btnHijo = hijo.querySelector('.btn-desplegar');
                    if (btnHijo) {
                        btnHijo.classList.remove('rotado');
                        btnHijo.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        }

        function ocultarDescendencia(padreId) {
            const descendientes = document.querySelectorAll(`.fila-tarea[data-parent="${padreId}"]`);
            descendientes.forEach(desc => {
                desc.style.display = 'none';
                const btnDesc = desc.querySelector('.btn-desplegar');
                if (btnDesc) btnDesc.classList.remove('rotado');
                ocultarDescendencia(desc.getAttribute('data-id'));
            });
        }
    </script>

@endsection