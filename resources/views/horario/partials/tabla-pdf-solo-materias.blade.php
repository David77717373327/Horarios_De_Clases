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
@endphp

<table style="table-layout: fixed; width: 100%;">
    <colgroup>
        <col style="width: 35px;">
        <col style="width: calc((100% - 35px) / 5);">
        <col style="width: calc((100% - 35px) / 5);">
        <col style="width: calc((100% - 35px) / 5);">
        <col style="width: calc((100% - 35px) / 5);">
        <col style="width: calc((100% - 35px) / 5);">
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
                <td class="hora-cell">{{ $hora }}</td>

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