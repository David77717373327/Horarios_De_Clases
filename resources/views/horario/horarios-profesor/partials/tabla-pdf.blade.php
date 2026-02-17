@php
    $dias = $config['dias_semana'];
    $horasPorDia = $config['horas_por_dia'];
    $recreoDespues = $config['recreo_despues_hora'];
    $recreoDuracion = $config['recreo_duracion'];
    $horas = range(1, $horasPorDia);

    if (!function_exists('calcularHoraProfesor')) {
        function calcularHoraProfesor($horaNumero, $config)
        {
            if (!isset($config['hora_inicio']) || !isset($config['duracion_clase'])) {
                return $horaNumero;
            }
            [$h, $m] = explode(':', $config['hora_inicio']);
            $minutos = $h * 60 + $m + ($horaNumero - 1) * $config['duracion_clase'];
            if (!empty($config['recreo_despues_hora']) && $horaNumero > $config['recreo_despues_hora']) {
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
        <col style="width: 72px;">
        @foreach ($dias as $dia)
            <col style="width: calc((100% - 72px) / {{ count($dias) }});">
        @endforeach
    </colgroup>

    <thead>
        <tr>
            <th class="col-hora-header">Hora</th>
            @foreach ($dias as $dia)
                <th>{{ $dia }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @foreach ($horas as $hora)
            <tr>
                <td class="hora-cell">{{ calcularHoraProfesor($hora, $config) }}</td>

                @foreach ($dias as $dia)
                    @php
                        $clases = $horarios[$dia][$hora] ?? null;
                    @endphp

                    @if ($clases && count($clases) > 0)
                        <td>
                            @foreach ($clases as $index => $clase)
                                @if ($index > 0)
                                    <hr class="clase-separador">
                                @endif
                                <span class="materia-nombre">{{ $clase['asignatura'] }}</span>
                                <span class="grado-info">{{ $clase['nivel'] }} · {{ $clase['grado'] }}</span>
                            @endforeach
                        </td>
                    @else
                        <td class="empty-cell">
                            <span class="libre-text">Libre</span>
                        </td>
                    @endif
                @endforeach
            </tr>

            @if($hora == $recreoDespues)
    @php
        // Calcular hora real del recreo: al terminar la hora $recreoDespues
        list($h, $m) = explode(':', $config['hora_inicio']);
        $minutosRecreo = ($h * 60) + $m + ($recreoDespues * $config['duracion_clase']);
        $horaRecreo = floor($minutosRecreo / 60);
        $minRecreo = $minutosRecreo % 60;
        $ampmRecreo = $horaRecreo < 12 ? 'AM' : 'PM';
        $hora12Recreo = $horaRecreo > 12 ? $horaRecreo - 12 : ($horaRecreo == 0 ? 12 : $horaRecreo);
        $horaTextoRecreo = sprintf('%d:%02d %s', $hora12Recreo, $minRecreo, $ampmRecreo);
    @endphp
    <tr class="recreo-row">
        <td class="hora-cell">{{ $horaTextoRecreo }}</td>
        <td colspan="{{ count($dias) }}">
            <span class="recreo-texto">Descanso · {{ $recreoDuracion }} minutos</span>
        </td>
    </tr>
@endif

        @endforeach
    </tbody>
</table>
