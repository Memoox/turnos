<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 6px;
        }
    </style>
</head>
<body>

    @php
        // Calculamos el total de columnas dinámicamente
        $totalColumnas = count($datos['tipos_turno']) + 1;
        
        // Calculamos las mitades para la tabla de abajo
        $mitad1 = floor($totalColumnas / 2);
        $mitad2 = ceil($totalColumnas / 2);
    @endphp

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <th colspan="{{ $totalColumnas }}" style="text-align: center; font-size: 14px; font-weight: bold; color: #0f172a;">
                REPORTE DEL SISTEMA DE TURNOS
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalColumnas }}" style="text-align: center; font-size: 12px; font-weight: bold; color: #334155;">
                {{ $datos['fecha_inicio'] }} al {{ $datos['fecha_fin'] }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalColumnas }}" style="text-align: center; font-size: 12px; font-weight: bold; color: #334155;">
                Estadística de {{ $datos['sede_nombre'] }}
            </th>
        </tr>
        <tr></tr> <tr>
            <th colspan="{{ $totalColumnas }}" style="background-color: #1e3a8a; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000;">
                Total de turnos asignados
            </th>
        </tr>
        <tr>
            <td colspan="{{ $totalColumnas }}" style="text-align: center; font-weight: bold; font-size: 14px; border: 1px solid #000000;">
                {{ $datos['total_turnos'] }}
            </td>
        </tr>
        <tr></tr> <tr>
            <th colspan="{{ $totalColumnas }}" style="background-color: #1e3a8a; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000;">
                Tiempos de atención
            </th>
        </tr>
        <tr>
            <th rowspan="2" style="background-color: #1e3a8a; color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000;">Hora</th>
            <th colspan="{{ $totalColumnas - 1 }}" style="background-color: #1e3a8a; color: #ffffff; text-align: center; border: 1px solid #000000;">Tipo de turno</th>
        </tr>
        <tr>
            @foreach($datos['tipos_turno'] as $tipo)
                <th style="background-color: #3b82f6; color: #ffffff; text-align: center; border: 1px solid #000000;">{{ $tipo }}</th>
            @endforeach
        </tr>
        
        @foreach($datos['matriz_tiempos'] as $hora => $tiemposPorTipo)
        <tr>
            <td style="text-align: center; border: 1px solid #000000;">{{ $hora }}</td>
            @foreach($datos['tipos_turno'] as $tipoId => $tipoNombre)
                <td style="text-align: center; border: 1px solid #000000;">{{ $tiemposPorTipo[$tipoId] ?? '0.00 min' }}</td>
            @endforeach
        </tr>
        @endforeach
        
        <tr>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000;">Promedio del día</th>
            @foreach($datos['tipos_turno'] as $tipoId => $tipoNombre)
                <th style="font-weight: bold; text-align: center; border: 1px solid #000000;">{{ $datos['promedios_generales'][$tipoId] ?? '0.00 min' }}</th>
            @endforeach
        </tr>
        <tr></tr> <tr>
            <th colspan="{{ $totalColumnas }}" style="background-color: #1e3a8a; color: #ffffff; text-align: center; font-weight: bold; border: 1px solid #000000;">
                Personas atendidas
            </th>
        </tr>
        <tr>
            <th colspan="{{ $mitad1 }}" style="background-color: #3b82f6; color: #ffffff; text-align: center; border: 1px solid #000000;">Hora</th>
            <th colspan="{{ $mitad2 }}" style="background-color: #3b82f6; color: #ffffff; text-align: center; border: 1px solid #000000;">No. usuarios</th>
        </tr>
        
        @foreach($datos['usuarios_por_hora'] as $hora => $total)
        <tr>
            <td colspan="{{ $mitad1 }}" style="text-align: center; border: 1px solid #000000;">{{ $hora }}</td>
            <td colspan="{{ $mitad2 }}" style="text-align: center; border: 1px solid #000000;">{{ $total }}</td>
        </tr>
        @endforeach
        
        <tr>
            <th colspan="{{ $mitad1 }}" style="font-weight: bold; text-align: center; border: 1px solid #000000;">Total</th>
            <th colspan="{{ $mitad2 }}" style="font-weight: bold; text-align: center; border: 1px solid #000000;">{{ $datos['total_atendidos'] }}</th>
        </tr>
    </table>

</body>
</html>