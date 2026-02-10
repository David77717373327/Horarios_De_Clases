<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario - {{ $profesorNombre }}</title>

    <style>
        @page {
            margin: 12mm 10mm;
            size: letter portrait;
        }

        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
        }
        
        .page-header {
            text-align: center; 
            margin: 0 0 12px 0; 
            font-size: 32px;
            font-weight: 900;
            color: #000;
            line-height: 1.2;
        }
        
        .profesor-title { 
            text-align: center; 
            margin: 0 0 15px 0;
            font-size: 28px;
            font-weight: 900;
            background: #d4edda;
            padding: 12px;
            color: #000;
            border: 2.5px solid #c3e6cb;
        }

        .horario-wrapper {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 2.5px solid #000;
            padding: 10px 6px;
            text-align: center;
            vertical-align: middle;
            height: 95px;
        }

        th {
            background: #a8d5a8;
            font-weight: 900;
            font-size: 22px;
            color: #000;
            height: 50px;
        }

        td {
            font-size: 15px;
            line-height: 1.4;
            background: #fff;
        }

        col.col-hora {
            width: 55px;
        }

        col.col-dia {
            width: calc((100% - 55px) / 5);
        }

        td.hora-cell {
            font-size: 28px;
            font-weight: 900;
            color: #000;
        }

        td strong {
            font-size: 18px;
            display: block;
            color: #000;
            font-weight: 900;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        td small {
            font-size: 14px;
            color: #333;
            font-weight: normal;
            display: block;
            line-height: 1.3;
        }

        .recreo-row td {
            background: #fff3cd;
            font-weight: 900;
            font-size: 20px;
            color: #000;
            height: 45px;
            padding: 10px;
        }

        .empty-cell {
            background: #f8f9fa !important;
            color: #999;
            font-style: italic;
            font-size: 14px;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="page-header">Horario del Profesor – Año {{ $year }}</div>

<div class="horario-wrapper">
    <div class="profesor-title">{{ $profesorNombre }}</div>

    @include('horario.horarios-profesor.partials.tabla-pdf', [
        'horarios' => $horarios,
        'config' => $config
    ])
</div>

<div class="footer">
    Generado el {{ date('d/m/Y H:i') }}
</div>

</body>
</html>