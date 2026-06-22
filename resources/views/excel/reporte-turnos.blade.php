<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* 🔥 1. Reducimos los márgenes del PDF para ganar espacio vital arriba y abajo */
        @page {
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px; /* Letra un poco más compacta */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid; /* Evita que las tablas se partan a la mitad */
        }
        th, td {
            padding: 4px; /* Relleno más ajustado (antes 6px) */
        }
        .text-center {
            text-align: center;
        }
        .border-all {
            border: 1px solid #000000;
        }
        .bg-primary {
            background-color: #1e3a8a;
            color: #ffffff;
        }
        .bg-secondary {
            background-color: #3b82f6;
            color: #ffffff;
        }
        /* 🔥 2. Evitamos que el texto se vaya a dos líneas y haga la fila más alta */
        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>
<body>

    @php
        $totalColumnas = count($datos['tipos_turno']) + 1;
        $mitad1 = floor($totalColumnas / 2);
        $mitad2 = ceil($totalColumnas / 2);
    @endphp

    <table>
        <tr>
            <th colspan="{{ $totalColumnas }}" class="text-center" style="font-size: 14px; font-weight: bold; color: #0f172a;">
                REPORTE DEL SISTEMA DE TURNOS
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalColumnas }}" class="text-center" style="font-size: 12px; font-weight: bold; color: #334155;">
                {{ $datos['fecha_inicio'] }} al {{ $datos['fecha_fin'] }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalColumnas }}" class="text-center" style="font-size: 12px; font-weight: bold; color: #334155;">
                Estadística de {{ $datos['sede_nombre'] }}
            </th>
        </tr>
        
        <tr><td colspan="{{ $totalColumnas }}" style="border: none; height: 10px;"></td></tr>

        <tr>
            <th colspan="{{ $totalColumnas }}" class="text-center bg-primary border-all" style="font-weight: bold;">
                Total de turnos asignados
            </th>
        </tr>
        <tr>
            <td colspan="{{ $totalColumnas }}" class="text-center border-all" style="font-weight: bold; font-size: 13px;">
                {{ $datos['total_turnos'] }}
            </td>
        </tr>
        
        <tr><td colspan="{{ $totalColumnas }}" style="border: none; height: 10px;"></td></tr>

        <tr>
            <th colspan="{{ $totalColumnas }}" class="text-center bg-primary border-all nowrap" style="font-weight: bold;">
                Tiempos de atención
            </th>
        </tr>
        <tr>
            <th rowspan="2" class="text-center bg-primary border-all" style="vertical-align: middle;">Hora</th>
            <th colspan="{{ $totalColumnas - 1 }}" class="text-center bg-primary border-all nowrap">Tipo de turno</th>
        </tr>
        <tr>
            @foreach($datos['tipos_turno'] as $tipo)
                <th class="text-center bg-secondary border-all nowrap">{{ $tipo }}</th>
            @endforeach
        </tr>
        
        @foreach($datos['matriz_tiempos'] as $hora => $tiemposPorTipo)
        <tr>
            <td class="text-center border-all nowrap">{{ $hora }}</td>
            @foreach($datos['tipos_turno'] as $tipoId => $tipoNombre)
                <td class="text-center border-all nowrap">{{ $tiemposPorTipo[$tipoId] ?? '0.00 min' }}</td>
            @endforeach
        </tr>
        @endforeach
        
        <tr>
            <th class="text-center border-all nowrap" style="font-weight: bold;">Promedio del día</th>
            @foreach($datos['tipos_turno'] as $tipoId => $tipoNombre)
                <th class="text-center border-all nowrap" style="font-weight: bold;">{{ $datos['promedios_generales'][$tipoId] ?? '0.00 min' }}</th>
            @endforeach
        </tr>
        
        <tr><td colspan="{{ $totalColumnas }}" style="border: none; height: 10px;"></td></tr>

        <tr>
            <th colspan="{{ $totalColumnas }}" class="text-center bg-primary border-all nowrap" style="font-weight: bold;">
                Personas atendidas
            </th>
        </tr>
        <tr>
            <th colspan="{{ $mitad1 }}" class="text-center bg-secondary border-all nowrap">Hora</th>
            <th colspan="{{ $mitad2 }}" class="text-center bg-secondary border-all nowrap">No. usuarios</th>
        </tr>
        
        @foreach($datos['usuarios_por_hora'] as $hora => $total)
        <tr>
            <td colspan="{{ $mitad1 }}" class="text-center border-all nowrap">{{ $hora }}</td>
            <td colspan="{{ $mitad2 }}" class="text-center border-all nowrap">{{ $total }}</td>
        </tr>
        @endforeach
        
        <tr>
            <th colspan="{{ $mitad1 }}" class="text-center border-all nowrap" style="font-weight: bold;">Total</th>
            <th colspan="{{ $mitad2 }}" class="text-center border-all nowrap" style="font-weight: bold;">{{ $datos['total_atendidos'] }}</th>
        </tr>
    </table>

</body>
</html>