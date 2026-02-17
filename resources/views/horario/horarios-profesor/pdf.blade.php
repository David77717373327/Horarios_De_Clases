<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario - {{ $profesorNombre }}</title>

    <style>
        @page {
            margin: 10mm 10mm;
            size: letter portrait;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #1a1a1a;
        }

        /* ============================
           ENCABEZADO
        ============================ */
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #555;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .header-logo {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
            text-align: center;
        }

        .header-logo img {
            width: 60px;
            height: 60px;
        }

        .header-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 10px;
        }

        .colegio-nombre {
            font-size: 18px;
            font-weight: 900;
            color: #1a1a1a;
            margin: 0 0 2px 0;
            letter-spacing: 0.5px;
        }

        .colegio-slogan {
            font-size: 10px;
            color: #555;
            margin: 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header-year {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            white-space: nowrap;
            padding-right: 0;
        }

        .year-box {
            background: #2d7a2d;
            color: #fff;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
            display: block;
            text-align: center;
        }

        /* ✅ SUBTÍTULO AÑO LECTIVO ALINEADO A LA DERECHA */
        .year-label {
            font-size: 9px;
            color: #777;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 3px;
            display: block;
            text-align: right;
        }

        /* ============================
           BANDA DEL PROFESOR
        ============================ */
        .profesor-banda {
            background: #f5f5f5;
            border-bottom: 1px solid #ccc;
            padding: 8px 12px;
            margin-bottom: 12px;
            display: table;
            width: 100%;
            box-sizing: border-box;
        }

        .profesor-label {
            font-size: 8px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #888;
            margin: 0 0 2px 0;
        }



        .profesor-nombre {
            font-size: 20px;
            font-weight: 900;
            color: #1a1a1a;
            margin: 0;
        }




        /* ============================
           TABLA
        ============================ */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* ✅ ENCABEZADOS TODOS BLANCOS, UN SOLO COLOR */
        th {
            background: #2c2c2c;
            color: #fff;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 10px 4px;
            border: 1.5px solid #000;
            text-align: center;
        }

        /* ✅ ENCABEZADO HORA MISMO COLOR QUE LOS DEMÁS */
        th.col-hora-header {
            background: #2c2c2c;
            color: #fff;
        }

        td {
            border: 1.5px solid #000;
            padding: 5px 4px;
            text-align: center;
            vertical-align: middle;
            height: 62px;
            background: #fff;
        }

        /* ✅ CELDA HORA - ESTILO LIMPIO SIN FONDO VERDE */
        td.hora-cell {
            background: #f0f0f0;
            font-size: 12px;
            font-weight: bold;
            color: #1a1a1a;
            border-right: 2px solid #000;
        }

        tr:nth-child(even) td {
            background: #fafafa;
        }

        tr:nth-child(even) td.hora-cell {
            background: #e8e8e8;
        }

        .materia-nombre {
            font-size: 11px;
            font-weight: bold;
            color: #1a1a1a;
            display: block;
            line-height: 1.3;
            margin-bottom: 2px;
        }




        .grado-info {
            font-size: 10px;
            color: #1a1a1a;
            display: block;
            line-height: 1.2;
            font-weight: bold;
        }



        

        td.empty-cell {
            background: #fafafa !important;
        }

        .libre-text {
            font-size: 9px;
            color: #ccc;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .clase-separador {
            border: none;
            border-top: 1px dashed #ccc;
            margin: 3px 0;
        }

        /* ============================
           FILA RECREO
        ============================ */
        tr.recreo-row td {
            background: #f5f5f5 !important;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            height: 26px;
            padding: 5px 4px;
        }

        tr.recreo-row td.hora-cell {
            background: #ececec !important;
            color: #444;
            /* ✅ LETRA RECREO MÁS GRANDE */
            font-size: 13px;
            font-weight: bold;
            border-right: 2px solid #000;
        }

        /* ✅ TEXTO RECREO MÁS GRANDE */
        .recreo-texto {
            font-size: 13px;
            font-weight: bold;
            color: #444;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        /* ============================
           PIE - ✅ SIN LÍNEA VERDE
        ============================ */
        .footer {
            border-top: 1px solid #ccc;
            margin-top: 12px;
            padding-top: 6px;
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            font-size: 8px;
            color: #888;
            letter-spacing: 1px;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .footer-right {
            display: table-cell;
            text-align: right;
            font-size: 8px;
            color: #888;
            letter-spacing: 1px;
            text-transform: uppercase;
            vertical-align: middle;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <div class="header">
        <div class="header-logo">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo">
            @endif
        </div>
        <div class="header-info">
            <p class="colegio-nombre">Gimnasio Humanístico</p>
            <p class="colegio-slogan">Gestión Educativa · Nieva Huila</p>
        </div>
        <div class="header-year">
            <span class="year-box">AÑO {{ $year }}</span>
            <span class="year-label">Año Lectivo</span>
        </div>
    </div>

    <!-- BANDA PROFESOR -->
    <div class="profesor-banda">
        <p class="profesor-label">Horario del Docente</p>
        <p class="profesor-nombre">{{ $profesorNombre }}</p>
    </div>

    <!-- TABLA -->
    @include('horario.horarios-profesor.partials.tabla-pdf', [
        'horarios' => $horarios,
        'config' => $config
    ])

    <!-- PIE -->
    <div class="footer">
        <div class="footer-left">Gimnasio Humanístico · Documento Oficial</div>
        <div class="footer-right">Generado el {{ date('d/m/Y H:i') }}</div>
    </div>

</body>
</html>