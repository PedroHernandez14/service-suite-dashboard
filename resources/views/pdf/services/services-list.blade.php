<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Servicios</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px; /* Letra un poco más pequeña para listas */
            color: #333;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #0D4D98;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
            color: #0D4D98;
            float: left;
        }
        .meta {
            float: right;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
            text-transform: uppercase;
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f3f4f6;
            color: #1f2937;
            font-weight: bold;
            padding: 8px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
            text-transform: uppercase;
            font-size: 10px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        /* Filas alternas (Zebra) */
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            color: white;
            font-size: 9px;
            font-weight: bold;
        }
        .bg-green { background-color: #10b981; }
        .bg-red { background-color: #ef4444; }
        .bg-gray { background-color: #6b7280; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">Multiservicios D.G.</div>
        <div class="meta">
            Generado: {{ now()->format('d/m/Y H:i') }}<br>
            Usuario: {{ auth()->user()->username ?? 'Admin' }}
        </div>
        <div style="clear: both;"></div>
    </div>

    <h1>Reporte General de Servicios</h1>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 20%">Categoría</th>
                <th style="width: 45%">Servicio / Descripción</th>
                <th style="width: 15%">Estado</th>
                <th style="width: 15%">Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td>#{{ $record->id }}</td>
                <td>{{ $record->serviceType->name ?? '-' }}</td>
                <td>{{ $record->description }}</td>
                <td>
                    @php
                        $status = $record->serviceType->status->name ?? '';
                        $color = match($status) {
                            'Activo' => 'bg-green',
                            'Inactivo' => 'bg-red',
                            default => 'bg-gray'
                        };
                    @endphp
                    <span class="badge {{ $color }}">{{ $status }}</span>
                </td>
                <td>{{ $record->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999;">
        Página generada por Service Suite System
    </div>

</body>
</html>
