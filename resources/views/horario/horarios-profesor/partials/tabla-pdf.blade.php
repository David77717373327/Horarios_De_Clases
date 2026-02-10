@php
    $dias = $config['dias_semana'];
    $horasPorDia = $config['horas_por_dia'];
    $recreoDespues = $config['recreo_despues_hora'];
    $recreoDuracion = $config['recreo_duracion'];
    
    $horas = range(1, $horasPorDia);
@endphp

<table style="table-layout: fixed; width: 100%;">
    <colgroup>
        <col class="col-hora">
        @foreach($dias as $dia)
            <col class="col-dia">
        @endforeach
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
                        $clases = isset($horarios[$dia][$hora]) ? $horarios[$dia][$hora] : null;
                    @endphp

                    <td @if(!$clases) class="empty-cell" @endif>
                        @if($clases && count($clases) > 0)
                            @foreach($clases as $index => $clase)
                                @if($index > 0)
                                    <hr style="margin: 4px 0; border: none; border-top: 2px solid #ccc;">
                                @endif
                                <strong>{{ $clase['asignatura'] }}</strong>
                                <small>{{ $clase['nivel'] }} - {{ $clase['grado'] }}</small>
                            @endforeach
                        @else
                            Libre
                        @endif
                    </td>
                @endforeach
            </tr>

            {{-- Fila de recreo --}}
            @if($hora == $recreoDespues)
                <tr class="recreo-row">
                    <td class="hora-cell">R</td>
                    <td colspan="{{ count($dias) }}">
                        <strong>RECREO ({{ $recreoDuracion }} min)</strong>
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>