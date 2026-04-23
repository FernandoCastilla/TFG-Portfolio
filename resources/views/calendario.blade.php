@extends('layouts.app')
@section('content')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

    <div class="mb-4">
        <x-breadcrumb :links="[
            ['label' => 'Panel Global', 'url' => url('/')],
            ['label' => 'Calendario de Tareas', 'url' => null],
        ]" />
    </div>

    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Calendario de Work Packages</h1>
            <p class="text-gray-600 dark:text-gray-400">Vista temporal de todas las tareas planificadas en los proyectos.</p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

            {{-- Leyenda organizada por grupos --}}
            <div class="flex flex-wrap gap-x-6 gap-y-3 mb-5 text-xs text-gray-600 dark:text-gray-400">

                {{-- Grupo: Hitos (H_) → púrpura --}}
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#8B5CF6"></span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">Hito</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#C4B5FD"></span>
                    <span>Planificado</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#8B5CF6"></span>
                    <span>En progreso</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#6D28D9"></span>
                    <span>Finalizado</span>
                </div>

                <span class="text-gray-300 dark:text-gray-600 self-center">|</span>

                {{-- Grupo: Solicitudes (S_) → ámbar --}}
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#F59E0B"></span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">Solicitud</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#FCD34D"></span>
                    <span>Planificada</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#F59E0B"></span>
                    <span>En progreso</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#D97706"></span>
                    <span>Validada</span>
                </div>

                <span class="text-gray-300 dark:text-gray-600 self-center">|</span>

                {{-- Grupo: Tareas (T_) → azul --}}
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#3B82F6"></span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">Tarea</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#93C5FD"></span>
                    <span>Planificada</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#3B82F6"></span>
                    <span>En progreso</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#1D4ED8"></span>
                    <span>Finalizada</span>
                </div>

                <span class="text-gray-300 dark:text-gray-600 self-center">|</span>

                {{-- Estados estándar OpenProject --}}
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#64748B"></span>
                    <span>Nueva / Nuev@</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#10B981"></span>
                    <span>Activa</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#F59E0B"></span>
                    <span>Planificada</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm" style="background:#374151"></span>
                    <span>Cerrada</span>
                </div>
            </div>

            <div id='calendar'></div>
        </div>
    </div>

    {{-- Estilos para adaptar FullCalendar al modo oscuro --}}
    <style>
        .dark .fc { color: #e5e7eb; }
        .dark .fc-theme-standard td, .dark .fc-theme-standard th { border-color: #374151; }
        .dark .fc-theme-standard .fc-scrollgrid { border-color: #374151; }
        .dark .fc-col-header-cell { background-color: #1f2937; }
        .dark .fc-daygrid-day { background-color: #111827; }
        .dark .fc-daygrid-day:hover { background-color: #1f2937; }
        .dark .fc-day-today { background-color: #312e81 !important; }
        .dark .fc-button-primary { background-color: #4f46e5 !important; border-color: #4338ca !important; }
        .dark .fc-button-primary:hover { background-color: #4338ca !important; }
        .dark .fc-button-primary:not(:disabled).fc-button-active { background-color: #3730a3 !important; }
        .dark .fc-toolbar-title { color: #f9fafb; }
        .dark .fc-daygrid-day-number { color: #9ca3af; }
        .dark .fc-col-header-cell-cushion { color: #d1d5db; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            var eventos = {!! json_encode($eventos) !!};

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                firstDay: 1,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', list: 'Lista' },
                events: eventos,
                eventClick: function (info) {
                    window.location.href = info.event.url;
                    info.jsEvent.preventDefault();
                }
            });
            calendar.render();
        });
    </script>
@endsection