<?php
$horasTotales = 0;
$horasNocturnas = 0;
$horasNormales = 0;
$error = "";

// Recogemos los valores seleccionados en los desplegables (o usamos 22:00 y 06:00 por defecto)
$horaInicioNocturnidad = $_POST['inicio_nocturnidad'] ?? "22:00";
$horaFinNocturnidad = $_POST['fin_nocturnidad'] ?? "06:00";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entradaStr = $_POST['entrada'] ?? '';
    $salidaStr = $_POST['salida'] ?? '';

    if (!empty($entradaStr) && !empty($salidaStr)) {
        // Convertimos las fechas/horas introducidas a timestamps (segundos Epoch)
        $timestampEntrada = strtotime($entradaStr);
        $timestampSalida = strtotime($salidaStr);

        if ($timestampSalida <= $timestampEntrada) {
            $error = "La fecha y hora de salida debe ser posterior a la de entrada.";
        } else {
            // 1. Duración total del turno en segundos
            $segundosTotales = $timestampSalida - $timestampEntrada;
            $horasTotales = round($segundosTotales / 3600, 2);

            // 2. Cálculo de la nocturnidad corrigiendo el solape de madrugada
            $segundosNocturnosTotales = 0;
            
            list($hNocInicio, $mNocInicio) = explode(':', $horaInicioNocturnidad);
            list($hNocFin, $mNocFin) = explode(':', $horaFinNocturnidad);

            // CORRECCIÓN: Empezamos un día antes ($timestampEntrada - 86400 segundos) 
            // para evaluar correctamente turnos que empiezan de madrugada (ej. a las 00:00) 
            // y que pillan la recta final de la nocturnidad que arrancó la noche anterior.
            $diaActual = strtotime(date('Y-m-d', $timestampEntrada)) - 86400;
            $diaFin = strtotime(date('Y-m-d', $timestampSalida));

            while ($diaActual <= $diaFin) {
                // Inicio de la nocturnidad en el día evaluado
                $nocturnidadInicioHoy = strtotime(date('Y-m-d', $diaActual) . " " . $horaInicioNocturnidad);
                
                // Si el fin de nocturnidad es menor que el inicio (ej: 06:00 < 22:00), 
                // significa que termina al día siguiente.
                $offsetDiasFin = ($hNocFin < $hNocInicio || ($hNocFin == $hNocInicio && $mNocFin < $mNocInicio)) ? "+1 day" : "today";
                $nocturnidadFinHoy = strtotime(date('Y-m-d', $nocturnidadInicioHoy) . " " . $horaFinNocturnidad . " " . $offsetDiasFin);

                // Aplicamos la fórmula matemática de solape (max/min)
                $solapeInicio = max($timestampEntrada, $nocturnidadInicioHoy);
                $solapeFin = min($timestampSalida, $nocturnidadFinHoy);

                if ($solapeFin > $solapeInicio) {
                    $segundosNocturnosTotales += ($solapeFin - $solapeInicio);
                }

                $diaActual = strtotime("+1 day", $diaActual);
            }

            $horasNocturnas = round($segundosNocturnosTotales / 3600, 2);
            $horasNormales = round($horasTotales - $horasNocturnas, 2);
        }
    } else {
        $error = "Por favor, introduce la fecha y hora completas del fichaje.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calculadora de Turnos y Nocturnidad Dinámica</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; margin: 40px; color: #333; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 520px; margin: auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="datetime-local"], select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; }
        fieldset { border: 1px solid #ddd; border-radius: 6px; padding: 15px; margin-bottom: 20px; background: #fafafa; }
        legend { font-weight: bold; color: #007bff; padding: 0 5px; }
        button { background: #007bff; color: white; border: none; padding: 10px 15px; width: 100%; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .results { margin-top: 25px; border-top: 1px solid #eee; padding-top: 15px; }
        .results p { font-size: 16px; margin: 8px 0; }
        .error { color: red; font-weight: bold; }
        .inline-group { display: flex; gap: 10px; }
        .inline-group > div { flex: 1; }
    </style>
</head>
<body>

<div class="card">
    <h2>Control de Fichaje y Nocturnidad</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <!-- Bloque para configurar la nocturnidad de forma visual -->
        <fieldset>
            <legend>Definir Horas de Nocturnidad</legend>
            <div class="inline-group">
                <div class="form-group">
                    <label for="inicio_nocturnidad">Inicio Nocturnidad:</label>
                    <select id="inicio_nocturnidad" name="inicio_nocturnidad">
                        <?php
                        for ($i = 0; $i < 24; $i++) {
                            $horaStr = str_pad($i, 2, "0", STR_PAD_LEFT) . ":00";
                            $selected = ($horaStr === $horaInicioNocturnidad) ? 'selected' : '';
                            echo "<option value=\"$horaStr\" $selected>$horaStr</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fin_nocturnidad">Fin Nocturnidad:</label>
                    <select id="fin_nocturnidad" name="fin_nocturnidad">
                        <?php
                        for ($i = 0; $i < 24; $i++) {
                            $horaStr = str_pad($i, 2, "0", STR_PAD_LEFT) . ":00";
                            $selected = ($horaStr === $horaFinNocturnidad) ? 'selected' : '';
                            echo "<option value=\"$horaStr\" $selected>$horaStr</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </fieldset>

        <!-- Bloque del fichaje del empleado -->
        <div class="form-group">
            <label for="entrada">Entrada (Fecha y Hora):</label>
            <input type="datetime-local" id="entrada" name="entrada" required value="<?php echo $_POST['entrada'] ?? ''; ?>">
        </div>

        <div class="form-group">
            <label for="salida">Salida (Fecha y Hora):</label>
            <input type="datetime-local" id="salida" name="salida" required value="<?php echo $_POST['salida'] ?? ''; ?>">
        </div>

        <button type="submit">Calcular Turno</button>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)): ?>
        <div class="results">
            <h3>Resultados:</h3>
            <p><strong>Total Trabajadas:</strong> <?php echo $horasTotales; ?> horas</p>
            <p><strong>Horas Normales:</strong> <?php echo $horasNormales; ?> horas</p>
            <p style="color: #d9534f;"><strong>Horas Nocturnas:</strong> <?php echo $horasNocturnas; ?> horas</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>