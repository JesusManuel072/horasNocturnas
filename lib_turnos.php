<?php
/**
 * calcularTurno
 *
 * Devuelve segundos y horas exactas sin formatear.
 */
function calcularTurno($entradaStr, $salidaStr, $horaInicioNocturnidad = "22:00", $horaFinNocturnidad = "06:00") {

    $timestampEntrada = strtotime($entradaStr);
    $timestampSalida  = strtotime($salidaStr);

    if ($timestampEntrada === false || $timestampSalida === false) {
        return ["error" => "formato"];
    }
    if ($timestampSalida <= $timestampEntrada) {
        return ["error" => "orden"];
    }

    // Segundos totales trabajados
    $segundosTotales = $timestampSalida - $timestampEntrada;

    // Parseo horas nocturnidad
    list($hNocInicio, $mNocInicio) = explode(':', $horaInicioNocturnidad);
    list($hNocFin, $mNocFin)       = explode(':', $horaFinNocturnidad);

    // Inicializamos acumulador de segundos nocturnos
    $segundosNocturnosTotales = 0;

    // Determinamos rango de días a iterar
    $diaActual = strtotime(date('Y-m-d', $timestampEntrada));
    $diaFin    = strtotime(date('Y-m-d', $timestampSalida));

    // Si la entrada cae en la franja de madrugada (<= fin nocturnidad),
    // retrocedemos un día para captar la nocturnidad que empezó el día anterior.
    $horaEntradaStr = date('H:i', $timestampEntrada);
    if ($horaEntradaStr <= $horaFinNocturnidad) {
        $diaActual = strtotime("-1 day", $diaActual);
    }

    // Recorremos día a día y calculamos solape con la ventana de nocturnidad de cada día
    while ($diaActual <= $diaFin) {
        $nocturnidadInicioHoy = strtotime(date('Y-m-d', $diaActual) . ' ' . $horaInicioNocturnidad);

        $nocturnidadFinHoy = strtotime(date('Y-m-d', $diaActual) . ' ' . $horaFinNocturnidad);
        if ($nocturnidadFinHoy <= $nocturnidadInicioHoy) {
            $nocturnidadFinHoy = strtotime('+1 day', $nocturnidadFinHoy);
        }

        $solapeInicio = max($timestampEntrada, $nocturnidadInicioHoy);
        $solapeFin    = min($timestampSalida, $nocturnidadFinHoy);

        if ($solapeFin > $solapeInicio) {
            $segundosNocturnosTotales += ($solapeFin - $solapeInicio);
        }

        $diaActual = strtotime('+1 day', $diaActual);
    }

    // Protección: el solape no puede exceder los segundos totales
    if ($segundosNocturnosTotales > $segundosTotales) {
        $segundosNocturnosTotales = $segundosTotales;
    }

    // Cálculos finales
    $segundosNormales = $segundosTotales - $segundosNocturnosTotales;

    $horasTotales    = $segundosTotales / 3600.0;
    $horasNocturnas  = $segundosNocturnosTotales / 3600.0;
    $horasNormales   = $segundosNormales / 3600.0;

    return [
        "segundosTotales"   => (int)$segundosTotales,
        "horasTotales"      => (float)$horasTotales,
        "segundosNocturnos" => (int)$segundosNocturnosTotales,
        "horasNocturnas"    => (float)$horasNocturnas,
        "segundosNormales"  => (int)$segundosNormales,
        "horasNormales"     => (float)$horasNormales
    ];
}
