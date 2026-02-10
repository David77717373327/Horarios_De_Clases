<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario Académico</title>

    <style>
        @page {
            margin: 8mm 10mm;
            size: letter landscape;
        }

        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
        }
        
        .page-header {
            text-align: center; 
            margin: 0 0 8px 0; 
            font-size: 30px;
            font-weight: 900;
            color: #000;
        }
        


        .grado-title { 
            text-align: center; 
            margin: 3px 0 5px;
            font-size: 28px;
            font-weight: 900;
            background: #d4edda;
            padding: 4px;
            color: #000;
            border: 1px solid #c3e6cb;
        }


        

        .horario-wrapper {
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1.5px solid #000;
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
            height: 26px;
        }

        th {
            background: #a8d5a8;
            font-weight: 900;
            font-size: 18px;
            color: #000;
        }

        td {
            font-size: 12px;
            line-height: 1.1;
            background: #fff;
        }

        /* Columna de Hora - MUY PEQUEÑA (35px) */
        col.col-hora {
            width: 30px;
        }

        /* Columnas de días - distribuir el resto equitativamente */
        col.col-dia {
            width: calc((100% - 35px) / 5);
        }


        /* Números de hora - MÁS GRANDES Y MÁS NEGROS */
        td.hora-cell {
            font-size: 20px;
            font-weight: 900;
            color: #000;
        }







        /* Contenido de las celdas de horario */
        td strong {
            font-size: 16px;
            display: block;
            color: #000;
            font-weight: 900;
        }

        td small {
            font-size: 12px;
            color: #000;
            font-weight: normal;
        }

        .recreo-row td {
            background: #fff3cd;
            font-weight: 900;
            font-size: 16px;
            color: #000;
            height: 20px;
        }

        /* Letra R del recreo MÁS GRANDE Y NEGRA */
        .recreo-row td.hora-cell {
            font-size: 16px;
            font-weight: 900;
            color: #000;
        }

        .page-break {
            page-break-after: always;
        }

        /* Espaciador entre los dos horarios */
        .spacer {
            height: 10px;
        }
    </style>
</head>
<body>

@php
    $totalGrados = $horarios->count();
    $contador = 0;
@endphp

@foreach($horarios as $gradoId => $horariosGrado)

    {{-- Título de página solo en la primera de cada par --}}
    @if($contador % 2 == 0)
        <div class="page-header">Horario {{ $nivelNombre }} – Año {{ $year }}</div>
    @endif

    <div class="horario-wrapper">
        <div class="grado-title">{{ $horariosGrado->first()->grado->nombre }}</div>

        @include('horario.partials.tabla-pdf', [
            'horarios' => $horariosGrado
        ])
    </div>

    @php
        $contador++;
    @endphp

    {{-- Espaciador entre horarios en la misma página --}}
    @if($contador % 2 == 1 && $contador < $totalGrados)
        <div class="spacer"></div>
    @endif

    {{-- Salto de página cada 2 grados (excepto en el último) --}}
    @if($contador % 2 == 0 && $contador < $totalGrados)
        <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>







