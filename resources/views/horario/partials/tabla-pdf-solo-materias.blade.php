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

<table>
    <colgroup>
        <col style="width: 68px;">
        @foreach($dias as $dia)
            <col style="width: calc((100% - 68px) / {{ count($dias) }});">
        @endforeach
    </colgroup>

    <thead>
        <tr>
            <th class="col-hora-header">Hora</th>
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
                    @php $celda = $tabla[$hora][$dia] ?? null; @endphp

                    @if($celda)
                        <td>
                            <span class="materia-nombre">{{ $celda->asignatura->nombre }}</span>
                        </td>
                    @else
                        <td class="empty-cell"></td>
                    @endif
                @endforeach
            </tr>

            @if($hora == $recreoDespues)
                @php
                    list($h, $m) = explode(':', $configuracion['hora_inicio']);
                    $minutosRecreo = ($h * 60) + $m + ($recreoDespues * $configuracion['duracion_clase']);
                    $horaRecreo = floor($minutosRecreo / 60);
                    $minRecreo = $minutosRecreo % 60;
                    $ampmRecreo = $horaRecreo < 12 ? 'AM' : 'PM';
                    $hora12Recreo = $horaRecreo > 12 ? $horaRecreo - 12 : ($horaRecreo == 0 ? 12 : $horaRecreo);
                    $horaTextoRecreo = sprintf('%d:%02d %s', $hora12Recreo, $minRecreo, $ampmRecreo);
                @endphp
                <tr class="recreo-row">
                    <td class="hora-cell">{{ $horaTextoRecreo }}</td>
                    <td colspan="{{ count($dias) }}">
                        <span class="recreo-texto">Descanso · {{ $configuracion['recreo_duracion'] }} minutos</span>
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>