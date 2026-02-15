@php
    $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
    $first = $horarios->first();
    
    $horaInicio = 1;
    $horasPorDia = $first->horas_por_dia;
    $recreoDespues = $first->recreo_despues_hora;
    
    $horas = range($horaInicio, $horasPorDia);
    
    $tabla = [];
    foreach ($horarios as $h) {
        $tabla[$h->hora_numero][$h->dia_semana] = $h;
    }
    
    // ✅ FUNCIÓN PARA CALCULAR HORA REAL (con protección contra redeclaración)
    if (!function_exists('calcularHoraSoloMaterias')) {
        function calcularHoraSoloMaterias($horaNumero, $config) {
            if (!$config) return $horaNumero;
            
            list($h, $m) = explode(':', $config['hora_inicio']);
            $minutos = ($h * 60) + $m + (($horaNumero - 1) * $config['duracion_clase']);
            
            if ($config['recreo_despues_hora'] && $horaNumero > $config['recreo_despues_hora']) {
                $minutos += $config['recreo_duracion'];
            }
            
            $hora = floor($minutos / 60);
            $min = $minutos % 60;
            $ampm = $hora < 12 ? 'AM' : 'PM';
            $hora12 = $hora > 12 ? $hora - 12 : ($hora == 0 ? 12 : $hora);
            
            return sprintf('%d:%02d %s', $hora12, $min, $ampm);
        }
    }
@endphp

<table style="table-layout: fixed; width: 100%;">
    <colgroup>
        <col style="width: 70px;">
        <col style="width: calc((100% - 70px) / 5);">
        <col style="width: calc((100% - 70px) / 5);">
        <col style="width: calc((100% - 70px) / 5);">
        <col style="width: calc((100% - 70px) / 5);">
        <col style="width: calc((100% - 70px) / 5);">
    </colgroup>

    <thead>
        <tr>
            <th>Hora</th>
            @foreach($dias as $dia)
                <th>{{ $dia }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @foreach($horas as $hora)
            <tr>
                <td class="hora-cell">{{ calcularHoraSoloMaterias($hora, $configuracion) }}</td>

                @foreach($dias as $dia)
                    @php
                        $celda = $tabla[$hora][$dia] ?? null;
                    @endphp

                    <td>
                        @if($celda)
                            <strong>{{ $celda->asignatura->nombre }}</strong>
                        @endif
                    </td>
                @endforeach
            </tr>

            @if($hora == $recreoDespues)
                <tr class="recreo-row">
                    <td class="hora-cell">R</td>
                    <td colspan="{{ count($dias) }}">
                        <strong>DESCANSO</strong>
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>