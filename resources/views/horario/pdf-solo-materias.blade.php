<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario Académico - Solo Materias</title>

    <style>
        @page {
            margin: 8mm 8mm;
            size: letter landscape;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #1a1a1a;
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #555;
            padding-bottom: 9px;
            margin-bottom: 10px;
        }

        .header-logo {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
            text-align: center;
        }

        .header-logo img {
            width: 65px;
            height: 65px;
        }






        .header-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 10px;
            width: 160px;
        }

        .colegio-nombre {
            font-size: 24px;
            font-weight: 900;
            color: #1a1a1a;
            margin: 0 0 3px 0;
            letter-spacing: 0.5px;
        }

        .header-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .header-titulo {
            font-size: 26px;
            font-weight: 900;
            color: #1a1a1a;
            margin: 0 0 4px 0;
            letter-spacing: 0.5px;
        }

        .header-nivel {
            font-size: 16px;
            color: #555;
            margin: 0;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header-year {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            white-space: nowrap;
            width: 160px;
            padding-right: 0;
        }

        .year-box {
            background: #2d7a2d;
            color: #fff;
            padding: 8px 15px;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            display: block;
            text-align: center;
        }

        .year-label {
            font-size: 11px;
            color: #777;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 3px;
            display: block;
            text-align: right;
        }

        .grado-banda {
            background: #f5f5f5;
            border-bottom: 1px solid #ccc;
            padding: 8px 12px;
            margin-bottom: 10px;
            width: 100%;
            box-sizing: border-box;
        }

        .grado-label {
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #727272;
            margin: 0 0 3px 0;
        }

        .grado-nombre {
            font-size: 28px;
            font-weight: 900;
            color: #1a1a1a;
            margin: 0;
        }

        .horario-wrapper {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            background: #2c2c2c;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 8px 3px;
            border: 1.5px solid #000;
            text-align: center;
        }

        th.col-hora-header {
            background: #2c2c2c;
            color: #fff;
        }

        td {
            border: 1.5px solid #000;
            padding: 3px 3px;
            text-align: center;
            vertical-align: middle;
            height: 48px;
            background: #fff;
        }

        td.hora-cell {
            background: #f0f0f0;
            font-size: 15px;
            font-weight: bold;
            color: #1a1a1a;
        }

        tr:nth-child(even) td {
            background: #fafafa;
        }

        tr:nth-child(even) td.hora-cell {
            background: #e8e8e8;
        }

        .materia-nombre {
            font-size: 14px;
            font-weight: bold;
            color: #1a1a1a;
            display: block;
            line-height: 1.2;
        }

        td.empty-cell {
            background: #fafafa !important;
        }




        tr.recreo-row td {
            background: #f5f5f5 !important;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            height: 32px;
            padding: 3px;
        }

        tr.recreo-row td.hora-cell {
            background: #ececec !important;
            color: #444;
            font-size: 17px;
            font-weight: bold;
        }

        .recreo-texto {
            font-size: 16px;
            font-weight: bold;
            color: #444;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

@php
    $totalGrados = $horarios->count();
    $contador = 0;

    $logoPath = public_path('images/Logo.png');
    $logoBase64 = null;
    if (file_exists($logoPath)) {
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }
@endphp

@foreach($horarios as $gradoId => $horariosGrado)

    <!-- ENCABEZADO EN CADA GRADO -->
    <div class="header">
        <div class="header-logo">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo">
            @endif
        </div>
        <div class="header-info">
            <p class="colegio-nombre">Gimnasio Humanístico</p>
        </div>
        <div class="header-center">
            <p class="header-titulo">Horario Académico Oficial</p>
            <p class="header-nivel">{{ $nivelNombre }}</p>
        </div>
        <div class="header-year">
            <span class="year-box">AÑO {{ $year }}</span>
            <span class="year-label">Año Lectivo</span>
        </div>
    </div>

    <div class="horario-wrapper">
        <div class="grado-banda">
            <p class="grado-label">GRADO:</p>
            <p class="grado-nombre">{{ $horariosGrado->first()->grado->nombre }}</p>
        </div>

        @include('horario.partials.tabla-pdf-solo-materias', [
            'horarios' => $horariosGrado,
            'configuracion' => $configuracion
        ])
    </div>

    @php $contador++; @endphp

    @if($contador < $totalGrados)
        <div class="page-break"></div>
    @endif

@endforeach

</body>
</html>