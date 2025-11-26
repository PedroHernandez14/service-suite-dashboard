<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Servicio</title>
    <style>
        /* Estilos generales para PDF (DomPDF no soporta Flexbox bien, usa tablas o floats) */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #0D4D98; /* Tu color corporativo */
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0D4D98;
            text-transform: uppercase;
        }
        .meta {
            text-align: right;
            float: right;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #444;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th, .info-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        .info-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 30%;
            color: #555;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        /* Utilidad para badges simulados en PDF */
        .bg-gray { background-color: #6b7280; }
        .bg-green { background-color: #10b981; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="meta">
        Fecha: {{ now()->format('d/m/Y') }}<br>
        ID Ref: #{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}
    </div>
    <div class="logo">
        Multiservicios D.G.
    </div>
    <div style="clear: both;"></div>
</div>

<div class="title">Ficha de Servicio</div>

<table class="info-table">
    <tr>
        <th>Servicio:</th>
        <td>{{ $record->description }}</td>
    </tr>
    <tr>
        <th>Categoría:</th>
        <td>{{ $record->serviceType->name ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th>Estado:</th>
        <td>
            @php
                $color = match($record->serviceType->status->name ?? '') {
                    'Activo' => 'bg-green',
                    default => 'bg-gray'
                };
            @endphp
            <span class="badge {{ $color }}">
                    {{ $record->serviceType->status->name ?? 'Desconocido' }}
                </span>
        </td>
    </tr>
    <tr>
        <th>Fecha de Creación:</th>
        <td>{{ $record->created_at->format('d/m/Y H:i A') }}</td>
    </tr>
</table>

<div style="margin-top: 30px;">
    <strong>Descripción Detallada:</strong>
    <p style="text-align: justify; color: #666;">
        {{ $record->description }}
        <br><br>
        Este documento certifica la información registrada en nuestro sistema de gestión para el servicio mencionado.
    </p>
</div>

<div class="footer">
    Multiservicios D.G. - Sistema de Gestión Service Suite<br>
    Documento generado automáticamente.
</div>

</body>
</html>
