<?php
// post_test.php
// Colocar en la carpeta pública de WAMP (www o htdocs).
// Asegúrate de que lib_turnos.php está en la misma carpeta o ajusta la ruta.

require_once __DIR__ . '/lib_turnos.php'; // ajustar ruta si hace falta

// Helper: segundos desde h,m,s
function s($h, $m = 0, $sec = 0) {
    return $h*3600 + $m*60 + $sec;
}

// Helper: formatea segundos a HH:MM
function format_hhmm($seconds) {
    $seconds = max(0, (int)$seconds);
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    return sprintf("%02d:%02d", $h, $m);
}

// Casos de prueba: cada entrada es [entrada, salida, inicioNoct, finNoct, esperadoSegTot, esperadoSegNoc, esperadoSegNorm]
$tests = [
    ["2026-10-16 00:00", "2026-10-16 00:01", "22:00", "06:00", s(0,1), s(0,1), 0],
    ["2026-10-16 00:00", "2026-10-16 00:02", "22:00", "06:00", s(0,2), s(0,2), 0],
    ["2026-10-16 00:00", "2026-10-16 01:01", "22:00", "06:00", s(1,1), s(1,1), 0],
    ["2026-10-16 00:00", "2026-10-16 01:59", "22:00", "06:00", s(1,59), s(1,59), 0],
    ["2026-10-16 21:00", "2026-10-17 01:00", "22:00", "06:00", s(4,0), s(3,0), s(1,0)],
    ["2026-10-16 08:00", "2026-10-17 10:00", "22:00", "06:00", s(26,0), s(8,0), s(18,0)],
    ["2027-10-16 08:00", "2026-10-18 08:00", "22:00", "06:00", s(48,0), s(16,0), s(32,0)],
];

// Ejecutar pruebas y recoger resultados
$results = [];
foreach ($tests as $i => $t) {
    list($entrada, $salida, $iniN, $finN, $expTot, $expNoc, $expNorm) = $t;
    $res = calcularTurno($entrada, $salida, $iniN, $finN);

    // Normalizar respuesta en caso de error
    if (isset($res['error'])) {
        $results[] = [
            'case' => $i+1,
            'entrada' => $entrada,
            'salida' => $salida,
            'status' => 'ERROR',
            'error' => $res['error'],
            'expected' => [$expTot, $expNoc, $expNorm],
            'actual' => null
        ];
        continue;
    }

    $actTot = $res['segundosTotales'];
    $actNoc = $res['segundosNocturnos'];
    $actNorm = $res['segundosNormales'];

    $ok = ($actTot === $expTot) && ($actNoc === $expNoc) && ($actNorm === $expNorm);

    $results[] = [
        'case' => $i+1,
        'entrada' => $entrada,
        'salida' => $salida,
        'status' => $ok ? 'OK' : 'FAIL',
        'expected' => [$expTot, $expNoc, $expNorm],
        'actual' => [$actTot, $actNoc, $actNorm]
    ];
}

// Mostrar resultados en HTML
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Pruebas lib_turnos</title>
<style>
body{font-family:Arial,Helvetica,sans-serif;margin:20px}
table{border-collapse:collapse;width:100%}
th,td{border:1px solid #ddd;padding:8px;text-align:left}
th{background:#222;color:#fff}
.ok{color:green;font-weight:bold}
.fail{color:red;font-weight:bold}
.error{color:orange;font-weight:bold}
.small{font-size:0.9em;color:#555}
</style>
</head>
<body>
<h2>Resultados de pruebas</h2>
<table>
<thead>
<tr>
<th>#</th>
<th>Entrada → Salida</th>
<th>Estado</th>
<th>Esperado (seg / HH:MM)</th>
<th>Actual (seg / HH:MM)</th>
</tr>
</thead>
<tbody>
<?php foreach ($results as $r): ?>
<tr>
<td><?php echo $r['case']; ?></td>
<td>
    <div><?php echo htmlspecialchars($r['entrada']); ?> → <?php echo htmlspecialchars($r['salida']); ?></div>
    <div class="small">Noct: <?php echo htmlspecialchars($tests[$r['case']-1][2]) . ' - ' . htmlspecialchars($tests[$r['case']-1][3]); ?></div>
</td>
<td>
    <?php
        if ($r['status'] === 'OK') echo '<span class="ok">✔ OK</span>';
        elseif ($r['status'] === 'FAIL') echo '<span class="fail">✖ FAIL</span>';
        else echo '<span class="error">⚠ ' . htmlspecialchars($r['error']) . '</span>';
    ?>
</td>
<td>
    <?php
    if ($r['expected']) {
        list($et,$en,$enorm) = $r['expected'];
        echo "{$et} s / " . format_hhmm($et) . "<br>";
        echo "{$en} s / " . format_hhmm($en) . " (nocturnos)<br>";
        echo "{$enorm} s / " . format_hhmm($enorm) . " (normales)";
    }
    ?>
</td>
<td>
    <?php
    if ($r['actual']) {
        list($at,$an,$anorm) = $r['actual'];
        echo "{$at} s / " . format_hhmm($at) . "<br>";
        echo "{$an} s / " . format_hhmm($an) . " (nocturnos)<br>";
        echo "{$anorm} s / " . format_hhmm($anorm) . " (normales)";
    } else {
        echo '<span class="small">No hay resultado (error)</span>';
    }
    ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<p class="small">Nota: este script no modifica tu index.php. Si quieres probar index.php directamente, crea un post_test que incluya index.php, pero es más seguro mantener la lógica en <code>lib_turnos.php</code> y testearla aquí.</p>
</body>
</html>
