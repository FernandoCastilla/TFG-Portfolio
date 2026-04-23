@extends('layouts.app')
@section('content')

<style>
    .op-content p { margin-bottom: 0.75rem; }
    .op-content table { width: 100%; border-collapse: collapse; margin-top: 1rem; margin-bottom: 1rem; background-color: white; }
    .dark .op-content table { background-color: #1f2937; }
    .op-content td, .op-content th { border: 1px solid #d1d5db; padding: 0.75rem; vertical-align: top; }
    .dark .op-content td, .dark .op-content th { border-color: #374151; color: #e5e7eb; }
    .op-content ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 0.75rem; }
</style>

<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <x-breadcrumb :links="[
            ['label' => 'Panel Global', 'url' => url('/')],
            ['label' => 'Volver atrás', 'url' => url()->previous()],
            ['label' => 'Tarea #' . $tarea['id']]
        ]" />
    </div>

    <div class="bg-white dark:bg-gray-800 p-8 rounded-t-xl shadow-sm border border-gray-200 dark:border-gray-700 border-b-0">
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <span class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs px-3 py-1 rounded border border-gray-200 dark:border-gray-600 uppercase font-bold tracking-wider">
                {{ $tarea['_links']['type']['title'] ?? 'Tarea' }}
            </span>
            <span class="bg-teal-50 dark:bg-teal-900/40 text-teal-800 dark:text-teal-300 text-xs font-bold px-3 py-1 rounded-full">
                {{ $tarea['_links']['status']['title'] ?? 'N/A' }}
            </span>
            @if(isset($tarea['_links']['priority']['title']))
                @php
                    $prioridad = $tarea['_links']['priority']['title'];
                    $colorPrio = match($prioridad) {
                        'Inmediata', 'Alta' => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300',
                        'Baja' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300',
                        default => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300'
                    };
                @endphp
                <span class="{{ $colorPrio }} text-xs font-bold px-3 py-1 rounded-full">
                    Prioridad: {{ $prioridad }}
                </span>
            @endif
        </div>

        <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">{{ $tarea['subject'] }}</h1>
        <p class="text-gray-500 dark:text-gray-400 font-mono text-sm mb-6">ID: #{{ $tarea['id'] }}</p>

        @php $porcentaje = $tarea['percentageDone'] ?? 0; @endphp
        <div class="mb-2 flex justify-between items-end">
            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Progreso de la tarea</span>
            <span class="text-sm font-bold {{ $porcentaje == 100 ? 'text-green-600' : 'text-teal-700 dark:text-teal-400' }}">{{ $porcentaje }}%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 border border-gray-300 dark:border-gray-600">
            <div class="{{ $porcentaje == 100 ? 'bg-green-500' : 'bg-teal-600' }} h-3 rounded-full transition-all duration-500" style="width: {{ $porcentaje }}%"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-0">

        <div class="md:col-span-2 bg-white dark:bg-gray-800 p-8 rounded-bl-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200 mb-4 border-b dark:border-gray-700 pb-2">Descripción de la Tarea</h3>
            <div class="text-gray-700 dark:text-gray-300 op-content">
                @if(!empty($tarea['description']['html']))
                    {!! $tarea['description']['html'] !!}
                @elseif(!empty($tarea['description']['raw']))
                    <p>{{ $tarea['description']['raw'] }}</p>
                @else
                    <p class="text-gray-400 italic">Esta tarea no tiene descripción detallada.</p>
                @endif
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-gray-700/50 p-8 rounded-br-xl shadow-sm border border-gray-200 dark:border-gray-700 border-l-0">
            <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200 mb-4 border-b border-gray-200 dark:border-gray-600 pb-2">Detalles</h3>

            <div class="space-y-5">
                <div>
                    <span class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Asignado a</span>
                    @if(isset($tarea['_links']['assignee']['title']))
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-indigo-200 dark:bg-teal-800 text-teal-700 dark:text-teal-300 flex items-center justify-center text-sm font-bold">
                                {{ strtoupper(substr($tarea['_links']['assignee']['title'], 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $tarea['_links']['assignee']['title'] }}</span>
                        </div>
                    @else
                        <span class="text-gray-500 dark:text-gray-400 italic">Sin asignar</span>
                    @endif
                </div>

                <div>
                    <span class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Creado por</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $tarea['_links']['author']['title'] ?? 'Desconocido' }}</span>
                </div>

                @php $entidad = $tarea['customField3'] ?? null; @endphp
                @if($entidad)
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 p-4 rounded-lg shadow-sm">
                    <span class="block text-xs font-bold text-amber-800 dark:text-amber-400 uppercase tracking-wider mb-2">Entidad Solicitante</span>
                    <span class="block font-bold text-gray-900 dark:text-gray-100 text-sm mb-1">{{ $entidad }}</span>
                    @if(!empty($tarea['customField4']))
                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-2 pt-2 border-t border-amber-200/50 dark:border-amber-700/50">
                            <span class="font-semibold block mb-0.5">Persona de Contacto:</span>
                            {{ $tarea['customField4'] }}
                            @if(!empty($tarea['customField5']))
                                <br>
                                <a href="mailto:{{ $tarea['customField5'] }}" class="text-amber-700 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300 hover:underline flex items-center gap-1 mt-1 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    {{ $tarea['customField5'] }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                @endif

                <div class="bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-600 shadow-sm">
                    <div class="mb-2">
                        <span class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Fecha Inicio</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">
                            {{ !empty($tarea['startDate']) ? date('d/m/Y', strtotime($tarea['startDate'])) : 'No definida' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Fecha Fin (Due Date)</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">
                            {{ !empty($tarea['dueDate']) ? date('d/m/Y', strtotime($tarea['dueDate'])) : 'No definida' }}
                        </span>
                    </div>
                </div>

                @if(isset($tarea['_links']['parent']['title']))
                <div class="mt-4 p-3 bg-teal-50 dark:bg-teal-900/20 border border-teal-100 dark:border-indigo-800 rounded">
                    <span class="block text-xs font-bold text-teal-600 dark:text-teal-400 uppercase mb-1">Pertenece a (Épica/Padre)</span>
                    <span class="font-medium text-teal-700 dark:text-teal-300 text-sm">{{ $tarea['_links']['parent']['title'] }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection