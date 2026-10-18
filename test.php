<?php

require "index.php";

class Tester {

    public static $errores = 0;

    public static function check($desc, $expected, $actual) {
        if ($expected === $actual) {
            echo "✔ $desc\n";
        } else {
            echo "❌ $desc\n";
            echo "   Esperado: $expected\n";
            echo "   Recibido: $actual\n";
            self::$errores++;
        }
    }

    public static function resumen() {
        if (self::$errores === 0) {
            echo "\nTodo OK, bro. Código fino.\n";
        } else {
            echo "\nFallaron " . self::$errores . " pruebas.\n";
        }
    }
}

// PRUEBAS

Tester::check("1 minuto", "1 minutos", mostrarTiempo(60/3600));
Tester::check("2 minutos", "2 minutos", mostrarTiempo(120/3600));
Tester::check("01:01", "01:01 horas", mostrarTiempo((3600+60)/3600));
Tester::check("01:59", "01:59 horas", mostrarTiempo((3600+3540)/3600));

Tester::resumen();
