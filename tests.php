<?php
require __DIR__ . '/lib_turnos.php';

class Tester {
    public static $errores = 0;

    public static function check($desc, $expectedSeconds, $actual) {
        // Esperamos un array con claves 'segundosTotales','segundosNocturnos','segundosNormales'
        $keys = ['segundosTotales','segundosNocturnos','segundosNormales'];
        $ok = true;
        foreach ($keys as $k) {
            $exp = $expectedSeconds[$k] ?? null;
            $act = $actual[$k] ?? null;
            if ($exp !== $act) {
                $ok = false;
                break;
            }
        }
        if ($ok) {
            echo "✔ $desc\n";
        } else {
            echo "❌ $desc\n";
            echo "   Esperado: "; var_export($expectedSeconds); echo "\n";
            echo "   Recibido: "; var_export($actual); echo "\n";
            self::$errores++;
        }
    }

    public static function resumen() {
        if (self::$errores === 0) {
            echo "\nTodo OK. Todas las pruebas pasaron.\n";
        } else {
            echo "\nFallaron " . self::$errores . " pruebas.\n";
        }
    }
}

// Helper para construir expectativas en segundos
function s($h, $m = 0, $sec = 0) {
    return $h*3600 + $m*60 + $sec;
}

// PRUEBAS

// 1 minuto dentro de nocturnidad (00:00 - 00:01)
Tester::check(
    "1 minuto nocturno",
    [
        'segundosTotales' => 60,
        'segundosNocturnos' => 60,
        'segundosNormales' => 0
    ],
    calcularTurno("2026-10-16 00:00", "2026-10-16 00:01", "22:00", "06:00")
);

// 2 minutos
Tester::check(
    "2 minutos nocturnos",
    [
        'segundosTotales' => 120,
        'segundosNocturnos' => 120,
        'segundosNormales' => 0
    ],
    calcularTurno("2026-10-16 00:00", "2026-10-16 00:02", "22:00", "06:00")
);

// 01:01 (1h 1m) nocturnidad completa
Tester::check(
    "01:01 nocturno",
    [
        'segundosTotales' => s(1,1),
        'segundosNocturnos' => s(1,1),
        'segundosNormales' => 0
    ],
    calcularTurno("2026-10-16 00:00", "2026-10-16 01:01", "22:00", "06:00")
);

// 01:59
Tester::check(
    "01:59 nocturno",
    [
        'segundosTotales' => s(1,59),
        'segundosNocturnos' => s(1,59),
        'segundosNormales' => 0
    ],
    calcularTurno("2026-10-16 00:00", "2026-10-16 01:59", "22:00", "06:00")
);

// Cruce de medianoche 21:00 -> 01:00 (4h total, 3h nocturnas si nocturnidad 22:00-06:00)
Tester::check(
    "Cruce medianoche 21:00-01:00",
    [
        'segundosTotales' => s(4,0),
        'segundosNocturnos' => s(3,0),
        'segundosNormales' => s(1,0)
    ],
    calcularTurno("2026-10-16 21:00", "2026-10-17 01:00", "22:00", "06:00")
);

// Turno de 26 horas 08:00 -> 10:00 siguiente dia
Tester::check(
    "Turno 26 horas 08:00 -> 10:00",
    [
        'segundosTotales' => s(26,0),
        // nocturnidad 22:00-06:00 aparece una vez completa (8h) dentro de las 26h
        'segundosNocturnos' => s(8,0),
        'segundosNormales' => s(18,0)
    ],
    calcularTurno("2026-10-16 08:00", "2026-10-17 10:00", "22:00", "06:00")
);

// 48 horas con dos noches completas
Tester::check(
    "48 horas con dos noches",
    [
        'segundosTotales' => s(48,0),
        'segundosNocturnos' => s(16,0),
        'segundosNormales' => s(32,0)
    ],
    calcularTurno("2026-10-16 08:00", "2026-10-18 08:00", "22:00", "06:00")
);

Tester::resumen();
