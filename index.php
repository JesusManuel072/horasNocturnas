<?php
// Inicializamos variables
$horasTotales = 0;
$horasNocturnas = 0;
$horasNormales = 0;
$error = "";

// Horarios de nocturnidad por defecto seguros
$horaInicioNocturnidad = "22:00";
$horaFinNocturnidad = "06:00";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $inputInicioNoc = $_POST['inicio_nocturnidad'] ?? "22:00";
    $inputFinNoc = $_POST['fin_nocturnidad'] ?? "06:00";

    if (preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])$/', $inputInicioNoc) && preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])$/', $inputFinNoc)) {
        $horaInicioNocturnidad = $inputInicioNoc;
        $horaFinNocturnidad = $inputFinNoc;
    }

    $entradaStr = $_POST['entrada'] ?? '';
    $salidaStr = $_POST['salida'] ?? '';

    if (!empty($entradaStr) && !empty($salidaStr)) {
        $timestampEntrada = strtotime($entradaStr);
        $timestampSalida = strtotime($salidaStr);

        if ($timestampEntrada === false || $timestampSalida === false) {
            $error = "El formato de fecha y hora introducido no es válido.";
        } elseif ($timestampSalida <= $timestampEntrada) {
            $error = "La fecha y hora de salida debe ser posterior a la de entrada.";
        } else {
            // 1. Duración total del turno en segundos
            $segundosTotales = $timestampSalida - $timestampEntrada;

            // Convertimos a horas exactas sin redondear
            $horasTotales = $segundosTotales / 3600;

            // 2. Cálculo de la nocturnidad blindado 100% contra solapes fantasma
            $segundosNocturnosTotales = 0;
            
            list($hNocInicio, $mNocInicio) = explode(':', $horaInicioNocturnidad);
            list($hNocFin, $mNocFin) = explode(':', $horaFinNocturnidad);

            // Determinamos el día de inicio y fin del bucle
            $diaActual = strtotime(date('Y-m-d', $timestampEntrada));
            $diaFin = strtotime(date('Y-m-d', $timestampSalida));

            $horaEntradaStr = date('H:i', $timestampEntrada);

            // Si entramos de madrugada (ej: 00:00), venimos de la noche anterior.
            if ($horaEntradaStr <= $horaFinNocturnidad) {
                $diaActual = strtotime("-1 day", $diaActual);
            }

            while ($diaActual <= $diaFin) {
                $nocturnidadInicioHoy = strtotime(date('Y-m-d', $diaActual) . " " . $horaInicioNocturnidad);
                
                $offsetDiasFin = ($hNocFin < $hNocInicio || ($hNocFin == $hNocInicio && $mNocFin < $mNocInicio)) ? "+1 day" : "today";
                $nocturnidadFinHoy = strtotime(date('Y-m-d', $nocturnidadInicioHoy) . " " . $horaFinNocturnidad . " " . $offsetDiasFin);

                $solapeInicio = max($timestampEntrada, $nocturnidadInicioHoy);
                $solapeFin = min($timestampSalida, $nocturnidadFinHoy);

                if ($solapeFin > $solapeInicio) {
                    $segundosNocturnosTotales += ($solapeFin - $solapeInicio);
                }

                $diaActual = strtotime("+1 day", $diaActual);
            }

            // Corrección matemática pura de frontera
            if ($segundosNocturnosTotales > $segundosTotales) {
                $segundosNocturnosTotales = $segundosTotales;
            }

            // Horas nocturnas exactas
            $horasNocturnas = $segundosNocturnosTotales / 3600;

            // Horas normales exactas
            $horasNormales = $horasTotales - $horasNocturnas;
        }
    } else {
        $error = "Por favor, introduce la fecha y hora completas del fichaje.";
    }
}

// Función para mostrar minutos si el turno es menor de 1 hora
function mostrarTiempo($horas) {
    if ($horas < 1) {
        $min = round($horas * 60); // minutos exactos
        return $min . " minutos";
    } else {
        return round($horas, 2) . " horas";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calculadora de Turnos y Nocturnidad Segura</title>
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
        <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST">
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

        <div class="form-group">
            <label for="entrada">Entrada (Fecha y Hora):</label>
            <input type="datetime-local" id="entrada" name="entrada" required value="<?php echo isset($_POST['entrada']) ? htmlspecialchars($_POST['entrada'], ENT_QUOTES, 'UTF-8') : ''; ?>">
        </div>

        <div class="form-group">
            <label for="salida">Salida (Fecha y Hora):</label>
            <input type="datetime-local" id="salida" name="salida" required value="<?php echo isset($_POST['salida']) ? htmlspecialchars($_POST['salida'], ENT_QUOTES, 'UTF-8') : ''; ?>">
        </div>

        <button type="submit">Calcular Turno</button>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)): ?>
        <div class="results">
            <h3>Resultados:</h3>
            <p><strong>Total Trabajadas:</strong> <?php echo mostrarTiempo($horasTotales); ?></p>
            <p><strong>Horas Normales:</strong> <?php echo mostrarTiempo($horasNormales); ?></p>
            <p style="color: #d9534f;"><strong>Horas Nocturnas:</strong> <?php echo mostrarTiempo($horasNocturnas); ?></p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
