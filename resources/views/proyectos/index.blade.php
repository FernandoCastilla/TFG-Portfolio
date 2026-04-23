@extends('layouts.app')
@section('content')

<div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
    <x-breadcrumb :links="[
        ['label' => 'Panel Global', 'url' => url('/')],
        ['label' => 'Proyectos UGR']
    ]" />

    <form method="GET" action="{{ route('proyectos.index') }}"
        class="mb-8 bg-gray-50 dark:bg-gray-700/50 p-4 rounded border border-gray-200 dark:border-gray-600 flex flex-wrap gap-4 items-end">

        <div class="flex-1 min-w-[200px]">
            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-1">Nombre del Proyecto</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar..."
                class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-1">Estado</label>
            <select name="status" class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Todos</option>
                <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Solo Activos</option>
                <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Solo Inactivos</option>
            </select>
        </div>

        <div>
            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-1">Visibilidad</label>
            <select name="visibility" class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Todas</option>
                <option value="public" {{ $visibilityFilter === 'public' ? 'selected' : '' }}>Públicos</option>
                <option value="private" {{ $visibilityFilter === 'private' ? 'selected' : '' }}>Privados</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition h-[42px]">
                Aplicar Filtros
            </button>
            <a href="{{ route('proyectos.index') }}" class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 font-bold py-2 px-6 rounded transition h-[42px] flex items-center">
                Limpiar
            </a>
        </div>
    </form>

    <div class="overflow-x-auto">
        @php
            $padres = []; $hijos = [];
            foreach($proyectos as $proyecto) {
                $parentHref = $proyecto['_links']['parent']['href'] ?? null;
                if ($parentHref && str_contains($parentHref, '/api/v3/projects/')) {
                    $parentId = basename($parentHref);
                    $hijos[$parentId][] = $proyecto;
                } else { $padres[] = $proyecto; }
            }
            $proyectosOrdenados = [];
            foreach($padres as $padre) {
                $padre['es_hijo'] = false;
                $proyectosOrdenados[] = $padre;
                $padreId = $padre['id'];
                if (isset($hijos[$padreId])) {
                    foreach($hijos[$padreId] as $hijo) { $hijo['es_hijo'] = true; $proyectosOrdenados[] = $hijo; }
                    unset($hijos[$padreId]);
                }
            }
            foreach($hijos as $grupoHijos) {
                foreach($grupoHijos as $hijo) { $hijo['es_hijo'] = true; $proyectosOrdenados[] = $hijo; }
            }
        @endphp

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-800 dark:bg-gray-900 text-white">
                    <th class="p-3 border-b-2 border-gray-700">ID</th>
                    <th class="p-3 border-b-2 border-gray-700">Nombre del Proyecto</th>
                    <th class="p-3 border-b-2 border-gray-700">Fecha de Creación</th>
                    <th class="p-3 border-b-2 border-gray-700 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proyectosOrdenados as $proyecto)
                    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700/50 transition border-b border-gray-200 dark:border-gray-700 {{ $proyecto['es_hijo'] ? 'bg-gray-50 dark:bg-gray-700/20' : '' }}">

                        <td class="p-3 text-gray-600 dark:text-gray-400">
                            @if($proyecto['es_hijo'])
                                <div class="flex items-center gap-2 pl-4 text-gray-400 dark:text-gray-500" title="Subproyecto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                    <span>#{{ $proyecto['id'] }}</span>
                                </div>
                            @else
                                <span class="font-medium">#{{ $proyecto['id'] }}</span>
                            @endif
                        </td>

                        <td class="p-3 font-semibold {{ $proyecto['es_hijo'] ? 'text-gray-600 dark:text-gray-400 pl-8' : 'text-gray-800 dark:text-gray-100' }}">
                            {{ $proyecto['name'] }}
                        </td>

                        <td class="p-3 text-gray-600 dark:text-gray-400">{{ date('d/m/Y', strtotime($proyecto['createdAt'])) }}</td>

                        <td class="p-3 text-center">
                            <a href="{{ route('proyectos.tareas', $proyecto['id']) }}"
                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm inline-block shadow-sm">
                                Ver Detalles
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-6 text-center text-gray-500 dark:text-gray-400 text-lg">
                            No se encontraron proyectos con ese filtro.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection