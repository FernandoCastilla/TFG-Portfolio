@extends('layouts.app')

@section('content')

<style>
    .op-content p { margin-bottom: 0.75rem; }
    .op-content table { width: 100%; border-collapse: collapse; margin-top: 1rem; margin-bottom: 1rem; background-color: white; }
    .op-content td, .op-content th { border: 1px solid #d1d5db; padding: 0.75rem; vertical-align: top; }
    .op-content ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 0.75rem; }
    .op-content figure { margin: 0; }
</style>

<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <x-breadcrumb :links="[
        ['label' => 'Panel Global', 'url' => url('/')],
        ['label' => 'Proyectos UGR', 'url' => route('proyectos.index')],
        ['label' => $proyecto['name']]
    ]" />

    <div class="border-b pb-4 mb-6">
        <div class="flex justify-between items-center mb-2">
            <h1 class="text-4xl font-bold text-gray-800">{{ $proyecto['name'] }}</h1>
            <span class="text-gray-500 font-mono text-sm">ID: #{{ $proyecto['id'] }}</span>
        </div>

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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="flex flex-col gap-6">
            <div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">Descripción</h3>
                <div class="text-gray-700 bg-gray-50 p-5 rounded-lg border border-gray-200 overflow-x-auto op-content shadow-inner">
                    @if(!empty($proyecto['description']['html']))
                        {!! $proyecto['description']['html'] !!}
                    @else
                        <p class="text-gray-400 italic mb-0">No hay descripción disponible para este proyecto.</p>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">Explicación del Estado</h3>
                <div class="text-gray-700 bg-gray-50 p-5 rounded-lg border border-gray-200 overflow-x-auto op-content shadow-inner">
                    @if(!empty($proyecto['statusExplanation']['html']))
                        {!! $proyecto['statusExplanation']['html'] !!}
                    @else
                        <p class="text-gray-400 italic mb-0">No hay explicación de estado detallada.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">Información Técnica</h3>
                <ul class="text-gray-600 bg-gray-50 p-5 rounded-lg border border-gray-200 space-y-3 shadow-inner">
                    <li class="flex justify-between border-b border-gray-200 pb-2">
                        <strong>Identificador:</strong> 
                        <span class="font-mono text-teal-700">{{ $proyecto['identifier'] }}</span>
                    </li>
                    <li class="flex justify-between border-b border-gray-200 pb-2">
                        <strong>Creado el:</strong> 
                        <span>{{ date('d/m/Y H:i', strtotime($proyecto['createdAt'])) }}</span>
                    </li>
                    <li class="flex justify-between">
                        <strong>Última actualización:</strong> 
                        <span>{{ date('d/m/Y H:i', strtotime($proyecto['updatedAt'])) }}</span>
                    </li>
                </ul>
            </div>
            
            <div class="mt-8 text-center">
                <a href="{{ route('proyectos.tareas', $proyecto['id']) }}"
                    class="w-full bg-teal-700 hover:bg-teal-700 text-white font-bold py-4 px-8 rounded-lg shadow-md transition-all hover:shadow-lg inline-flex justify-center items-center text-lg">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                        </path>
                    </svg>
                    Ver Tareas del Proyecto
                </a>
            </div>
        </div>
    </div>
</div>

@endsection