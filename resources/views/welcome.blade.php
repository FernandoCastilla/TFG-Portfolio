@extends('layouts.app')

@section('content')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-7xl mx-auto">

        <div class="bg-gradient-to-br from-teal-900 via-teal-800 to-teal-700 rounded-2xl p-8 mb-8 text-white shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold mb-2 tracking-tight">Centro de Mando UGR</h1>
                    <p class="text-teal-100 text-lg max-w-xl">
                        Monitorización en tiempo real de Objetivos, KPIs y Work Packages del Vicerrectorado.
                    </p>
                </div>
                
                <button id="btn-sync" onclick="sincronizarDatos()" class="group relative bg-teal-600 hover:bg-teal-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all flex items-center gap-3 overflow-hidden border border-teal-500 hover:border-white">
                    <svg id="sync-icon" class="w-6 h-6 transition-transform group-hover:rotate-180 duration-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span id="sync-text">Sincronizar con OpenProject</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex items-center hover:shadow-md transition-shadow">
                <div class="p-4 bg-teal-50 text-teal-700 rounded-xl mr-5">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Proyectos Activos</p>
                    <p class="text-3xl font-black text-gray-800 dark:text-white">{{ $totalProyectos }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex items-center hover:shadow-md transition-shadow">
                <div class="p-4 bg-orange-50 text-orange-600 rounded-xl mr-5">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tareas (W.P.)</p>
                    <p class="text-3xl font-black text-gray-800 dark:text-white">{{ $totalTareas }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex items-center hover:shadow-md transition-shadow">
                <div class="p-4 bg-green-50 text-green-600 rounded-xl mr-5">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">API UGR</p>
                    @if($totalProyectos > 0)
                        <p class="text-xl font-black text-green-600 uppercase">Conectada</p>
                        @if($ultimaSync)
                            <p class="text-xs text-gray-400 dark:text-gray-500 font-medium mt-1">
                                Sync {{ $ultimaSync->tiempoTranscurrido }} · {{ $ultimaSync->duracion_segundos }}s
                            </p>
                        @else
                            <p class="text-xs text-green-500 font-medium mt-1">Sincronización OK</p>
                        @endif
                    @else
                        <p class="text-xl font-black text-red-600 uppercase">Sin Datos</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- WIDGET HISTORIAL DE SINCRONIZACIONES --}}
        {{-- WIDGET DE ACTIVIDAD RECIENTE --}}
        @if(!empty($actividadReciente))
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-8 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-700/30">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Actividad Reciente
                </h2>
                <span class="text-xs text-gray-400 dark:text-gray-500">Últimas tareas modificadas</span>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($actividadReciente as $item)
                <a href="{{ $item['url'] }}"
                   class="flex items-center gap-4 px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition group">

                    {{-- Icono de estado --}}
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-teal-50 dark:bg-teal-900/30 flex items-center justify-center">
                        <span class="text-xs font-black text-teal-700 dark:text-teal-400">
                            {{ strtoupper(substr($item['estado'], 0, 1)) }}
                        </span>
                    </div>

                    {{-- Info principal --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate group-hover:text-teal-700 dark:group-hover:text-teal-400 transition-colors">
                            {{ $item['subject'] }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate mt-0.5">
                            {{ $item['proyecto'] }}
                        </p>
                    </div>

                    {{-- Estado --}}
                    <span class="hidden sm:block flex-shrink-0 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded-lg">
                        {{ $item['estado'] }}
                    </span>

                    {{-- Tiempo transcurrido --}}
                    <span class="flex-shrink-0 text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($item['updatedAt'])->diffForHumans() }}
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        @if($historialSync->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-8 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-700/30">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Historial de Sincronizaciones
                </h2>
                <span class="text-xs text-gray-400 dark:text-gray-500">Últimas {{ $historialSync->count() }} ejecuciones</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <th class="px-5 py-3 text-left">Fecha y hora</th>
                            <th class="px-5 py-3 text-center">Duración</th>
                            <th class="px-5 py-3 text-center">Proyectos API</th>
                            <th class="px-5 py-3 text-center">Tareas API</th>
                            <th class="px-5 py-3 text-center">Nuevos proyectos</th>
                            <th class="px-5 py-3 text-center">Nuevas tareas</th>
                            <th class="px-5 py-3 text-center">Total proyectos</th>
                            <th class="px-5 py-3 text-center">Total tareas</th>
                            <th class="px-5 py-3 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                        @foreach($historialSync as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            <td class="px-5 py-3 text-gray-700 dark:text-gray-300 font-medium whitespace-nowrap">
                                {{ $log->ejecutado_en_fmt }}
                                <span class="block text-xs text-gray-400 dark:text-gray-500">{{ $log->tiempoTranscurrido }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="font-mono font-bold {{ $log->duracion_segundos > 10 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $log->duracion_segundos }}s
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center text-gray-600 dark:text-gray-400">{{ $log->proyectos_api }}</td>
                            <td class="px-5 py-3 text-center text-gray-600 dark:text-gray-400">{{ $log->tareas_api }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($log->proyectos_nuevos > 0)
                                    <span class="inline-block bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-bold px-2 py-0.5 rounded-full text-xs">+{{ $log->proyectos_nuevos }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($log->tareas_nuevas > 0)
                                    <span class="inline-block bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-bold px-2 py-0.5 rounded-full text-xs">+{{ $log->tareas_nuevas }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center text-gray-600 dark:text-gray-400 font-medium">{{ $log->proyectos_total }}</td>
                            <td class="px-5 py-3 text-center text-gray-600 dark:text-gray-400 font-medium">{{ $log->tareas_total }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($log->estado === 'ok')
                                    <span class="inline-flex items-center gap-1 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-xs font-bold px-2 py-1 rounded-full border border-green-200 dark:border-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> OK
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-xs font-bold px-2 py-1 rounded-full border border-red-200 dark:border-red-700" title="{{ $log->mensaje_error }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span> Error
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-700/30">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">Últimos Proyectos Detectados</h2>
                    <a href="{{ route('proyectos.index') }}" class="text-sm font-bold text-teal-700 hover:text-teal-800 transition-colors">Ver todos &rarr;</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700 flex-1 overflow-y-auto" style="max-height: 400px;">
                    @forelse($ultimosProyectos as $proyecto)
                        <div class="p-5 hover:bg-teal-50/30 dark:hover:bg-teal-900/10 transition flex justify-between items-center group">
                            <div>
                                <p class="text-xs text-teal-400 font-mono font-bold mb-1">
                                    PROYECTO #{{ $proyecto['id'] }}
                                    <span class="text-gray-300 mx-1">·</span>
                                    <span class="text-gray-400 font-sans font-normal normal-case">
                                        {{ \Carbon\Carbon::parse($proyecto['createdAt'])->format('d/m/Y') }}
                                    </span>
                                </p>
                                <h3 class="text-md font-bold text-gray-800 dark:text-gray-100 group-hover:text-teal-700 transition-colors">{{ $proyecto['name'] }}</h3>
                            </div>
                            <a href="{{ route('proyectos.tareas', $proyecto['id']) }}" class="opacity-0 group-hover:opacity-100 bg-white dark:bg-gray-700 text-teal-700 dark:text-teal-400 border border-teal-200 dark:border-indigo-700 hover:bg-teal-700 hover:text-white px-4 py-2 rounded-lg text-sm font-bold transition shadow-sm">
                                Abrir
                            </a>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500 dark:text-gray-400 text-sm italic">
                            No hay proyectos disponibles. Pulsa en sincronizar.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- GRÁFICA CON SELECTOR --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 flex flex-col">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Distribución Global</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Estado de los Work Packages del Vicerrectorado.</p>
                    </div>

                    {{-- DESPLEGABLE DE SELECCIÓN --}}
                    <div class="flex-shrink-0">
                        <select id="selector-grafica" onchange="actualizarGrafica(this.value)"
                            class="text-sm font-semibold bg-teal-50 border border-teal-200 text-teal-800 rounded-xl px-4 py-2.5 cursor-pointer focus:outline-none focus:ring-2 focus:ring-teal-400 transition-colors hover:bg-teal-50">
                            <option value="solicitudes" selected>Solicitudes</option>
                            @auth
                                <option value="tareas">Tareas</option>
                                <option value="hitos">Hitos</option>
                            @endauth
                            <option value="estandar">Estados Estándar (OpenProject)</option>
                            <option value="todos">Todos los estados</option>
                        </select>
                    </div>
                </div>

                <div class="relative w-full flex-1 flex items-center justify-center min-h-[300px]">
                    @if(count($graficaDatos) > 0)
                        <canvas id="graficaGlobal"></canvas>
                    @else
                        <div class="text-center">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                            <p class="text-gray-400 font-medium">No hay datos para la gráfica.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

    {{-- PANEL LATERAL DE TAREAS POR ESTADO --}}
    <div id="panel-tareas" class="fixed top-0 right-0 h-full w-full max-w-md bg-white dark:bg-gray-800 shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col border-l border-gray-200">
        
        {{-- Cabecera del panel --}}
        <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-700">
            <div>
                <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Estado seleccionado</p>
                <h3 id="panel-titulo" class="text-lg font-extrabold text-gray-800 dark:text-white"></h3>
            </div>
            <button onclick="cerrarPanel()" aria-label="Cerrar panel de tareas"
                class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Badge con el conteo --}}
        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
            <span id="panel-conteo" class="text-sm font-bold text-teal-700 bg-teal-50 border border-teal-100 px-3 py-1 rounded-full"></span>
        </div>

        {{-- Lista de tareas --}}
        <div id="panel-lista" class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
            {{-- Se rellena con JS --}}
        </div>
    </div>

    {{-- Overlay oscuro de fondo --}}
    <div id="panel-overlay" onclick="cerrarPanel()" class="hidden fixed inset-0 bg-black bg-opacity-30 z-40 backdrop-blur-sm"></div>

    <script>
        const COLORES = [
            '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#3B82F6',
            '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#64748B',
            '#06B6D4', '#A855F7', '#84CC16', '#FB923C', '#E11D48'
        ];

        const todosLabels = {!! json_encode($graficaLabels) !!};
        const todosDatos  = {!! json_encode($graficaDatos) !!};
        const todasTareas = {!! json_encode($tareasCompactas) !!};

        // Limpia etiquetas UGR para mostrarlas de forma legible.
        // Ej: "S_Progreso_50%" → "Progreso al 50%"
        //     "H_Planificado_5%" → "Planificado al 5%"
        //     "Nuev@" → "Nuev@" (sin cambios)
        function limpiarEtiqueta(label) {
            return label
                .replace(/^[STH]_/, '')           // quita prefijo S_, T_, H_
                .replace(/_(\d+)%$/, ' al $1%');  // convierte _50% en " al 50%"
        }

        // Devuelve true si la etiqueta pertenece a los estados UGR personalizados
        function esEstadoUGR(label) {
            return /^[STH]_/.test(label) || label === 'Nuev@';
        }

        // Filtra los datos según la vista y devuelve etiquetas limpias + originales.
        // rawLabels conserva el valor original para filtrar tareas en el panel lateral.
        function filtrarDatos(vista) {
            const labels = [], rawLabels = [], datos = [], colores = [];

            todosLabels.forEach((label, i) => {
                const incluir =
                    vista === 'todos'       ||
                    (vista === 'solicitudes' && label.startsWith('S_')) ||
                    (vista === 'tareas'      && label.startsWith('T_')) ||
                    (vista === 'hitos'       && label.startsWith('H_')) ||
                    (vista === 'estandar'    && !esEstadoUGR(label));

                if (incluir) {
                    // Para las vistas por prefijo mostramos la etiqueta limpia;
                    // para 'estandar' y 'todos' mostramos el valor original.
                    const etiqueta = ['solicitudes', 'tareas', 'hitos'].includes(vista)
                        ? limpiarEtiqueta(label)
                        : label;

                    labels.push(etiqueta);
                    rawLabels.push(label);   // original, para filtrar todasTareas
                    datos.push(todosDatos[i]);
                    colores.push(COLORES[labels.length - 1 % COLORES.length]);
                }
            });

            return { labels, rawLabels, datos, colores };
        }

        let graficaInstance = null;
        // Guardamos rawLabels del render actual para usarlos al clicar la gráfica
        let rawLabelsActuales = [];

        function actualizarGrafica(vista) {
            const { labels, rawLabels, datos, colores } = filtrarDatos(vista);
            rawLabelsActuales = rawLabels;

            if (graficaInstance) {
                graficaInstance.data.labels = labels;
                graficaInstance.data.datasets[0].data = datos;
                graficaInstance.data.datasets[0].backgroundColor = colores;
                graficaInstance.update();
            }
        }

        // --- PANEL LATERAL ---
        // estadoOriginal: valor real del campo status para filtrar tareas
        // etiquetaLimpia: texto legible para mostrar en la cabecera del panel
        function abrirPanel(estadoOriginal, etiquetaLimpia, color) {
            const tareasFiltradas = todasTareas.filter(t => t.status === estadoOriginal);

            document.getElementById('panel-titulo').textContent = etiquetaLimpia;
            document.getElementById('panel-titulo').style.color = color;
            document.getElementById('panel-conteo').textContent =
                tareasFiltradas.length + ' tarea' + (tareasFiltradas.length !== 1 ? 's' : '');

            const lista = document.getElementById('panel-lista');
            if (tareasFiltradas.length === 0) {
                lista.innerHTML = '<p class="p-6 text-center text-gray-400 italic text-sm">No hay tareas con este estado.</p>';
            } else {
                lista.innerHTML = tareasFiltradas.map(t => `
                    <a href="${t.url}" class="flex flex-col gap-1 p-4 hover:bg-teal-50 transition group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono text-gray-400">#${t.id}</span>
                            ${t.assignee ? `<span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">${t.assignee}</span>` : ''}
                        </div>
                        <p class="text-sm font-semibold text-gray-800 group-hover:text-teal-700 transition-colors leading-snug">${t.subject}</p>
                        <p class="text-xs text-gray-400">${t.project}</p>
                    </a>
                `).join('');
            }

            document.getElementById('panel-tareas').classList.remove('translate-x-full');
            document.getElementById('panel-overlay').classList.remove('hidden');
        }

        function cerrarPanel() {
            document.getElementById('panel-tareas').classList.add('translate-x-full');
            document.getElementById('panel-overlay').classList.add('hidden');
        }

        document.addEventListener("DOMContentLoaded", function () {
            const canvas = document.getElementById('graficaGlobal');
            if (!canvas) return;

            // Vista por defecto: Solicitudes (S_), legible para el público general
            const { labels, rawLabels, datos, colores } = filtrarDatos('solicitudes');
            rawLabelsActuales = rawLabels;

            graficaInstance = new Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: datos,
                        backgroundColor: colores,
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 16, usePointStyle: true, font: { family: 'sans-serif', weight: 'bold' } }
                        }
                    },
                    cutout: '70%',
                    onClick: (event, elements) => {
                        if (!elements.length) return;
                        const idx            = elements[0].index;
                        const etiquetaLimpia = graficaInstance.data.labels[idx];
                        const estadoOriginal = rawLabelsActuales[idx];
                        const color          = graficaInstance.data.datasets[0].backgroundColor[idx];
                        abrirPanel(estadoOriginal, etiquetaLimpia, color);
                    },
                    onHover: (event, elements) => {
                        event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                    }
                }
            });
        });

        // Sincronización AJAX
        function sincronizarDatos() {
            const btn  = document.getElementById('btn-sync');
            const icon = document.getElementById('sync-icon');
            const text = document.getElementById('sync-text');

            btn.disabled = true;
            btn.classList.replace('bg-teal-600', 'bg-teal-800');
            icon.classList.add('animate-spin');
            text.innerText = 'Sincronizando...';

            fetch('{{ route('api.sync') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    text.innerText = '¡Actualizado!';
                    btn.classList.replace('bg-teal-800', 'bg-green-500');
                    icon.classList.remove('animate-spin');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    alert('Error al sincronizar: ' + data.message);
                    resetBtn(btn, icon, text);
                }
            })
            .catch(() => { alert('Fallo de conexión.'); resetBtn(btn, icon, text); });
        }

        function resetBtn(btn, icon, text) {
            btn.disabled = false;
            btn.classList.replace('bg-teal-800', 'bg-teal-600');
            icon.classList.remove('animate-spin');
            text.innerText = 'Sincronizar con OpenProject';
        }
    </script>

@endsection