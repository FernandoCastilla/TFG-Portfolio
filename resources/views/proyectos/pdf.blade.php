<!-- DomPDF es un poco antiguo leyendo CSS. No entiende bien Tailwind, así que para los PDFs siempre se usa CSS puro y duro -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Proyecto</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #4F46E5; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 24px; color: #111; margin: 0; }
        .subtitle { font-size: 14px; color: #666; }
        .stats-box { background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #4F46E5; color: white; padding: 10px; text-align: left; font-size: 12px; }
        td { border-bottom: 1px solid #ddd; padding: 10px; font-size: 11px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .badge { padding: 3px 6px; border-radius: 3px; font-weight: bold; font-size: 10px; }
        .status-badge { background-color: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
        .footer { position: absolute; bottom: -20px; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">Tracker UGR - Informe de Proyecto</h1>
        <p class="subtitle">Generado el {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="stats-box">
        <h2 style="margin-top: 0; color: #4F46E5;">Proyecto: {{ $proyecto['name'] }}</h2>
        <p><strong>ID OpenProject:</strong> #{{ $proyecto['id'] }}</p>
        <p><strong>Total de Work Packages (Tareas):</strong> {{ count($tareas) }}</p>
    </div>

    <h3>Listado de Tareas</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Asunto</th>
                <th>Estado</th>
                <th>Asignado a</th>
                <th>Progreso</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tareas as $tarea)
                <tr>
                    <td>#{{ $tarea['id'] }}</td>
                    <td>{{ $tarea['subject'] }}</td>
                    <td><span class="badge status-badge">{{ $tarea['_links']['status']['title'] ?? 'N/A' }}</span></td>
                    <td>{{ $tarea['_links']['assignee']['title'] ?? 'Sin asignar' }}</td>
                    <td>{{ $tarea['percentageDone'] ?? 0 }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No hay tareas registradas para este proyecto.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Sistema de Gestión UGR Portfolio - Trabajo de Fin de Grado
    </div>

</body>
</html>