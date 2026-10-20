
🕒 Sistema de Cálculo de Turnos con Nocturnidad

Proyecto en PHP para calcular turnos laborales con precisión, incluyendo:

duración total del turno

horas nocturnas

horas normales

solapamientos entre días

cruces de medianoche

turnos largos (24h, 48h, etc.)

validación de fechas y horas

presentación en minutos o HH:MM

El objetivo es ofrecer un sistema fiable, claro y fácil de integrar en aplicaciones reales.

📁 Estructura del proyecto

/
├── index.php
├── lib_turnos.php
├── tests.php
├── post_test.php
└── README.md

📄 index.php

Interfaz web del proyecto.

Recoge los datos del formulario (entrada, salida, inicio_nocturnidad, fin_nocturnidad).

Llama a la función principal:

$result = calcularTurno($entrada, $salida, $inicioNoc, $finNoc);

Muestra los resultados en pantalla.

Incluye un ejemplo comentado para guardar los datos en base de datos mediante PDO.

Importante:index.php no contiene la lógica de cálculo.Toda la matemática está en lib_turnos.php.

📄 lib_turnos.php

Motor del sistema.

Contiene la función:

calcularTurno($entradaStr, $salidaStr, $horaInicioNocturnidad, $horaFinNocturnidad)

Esta función calcula:

segundos totales

horas totales

segundos nocturnos

horas nocturnas

segundos normales

horas normales

Incluye:

validación de formato

validación de orden (salida > entrada)

cálculo de solapamientos nocturnos sin errores

soporte para turnos de varios días

corrección matemática de fronteras

Es la lógica que usan index.php, tests.php y post_test.php.

📄 tests.php

Pruebas automáticas por CLI.

Permite validar la lógica con casos reales y casos límite:

turnos de minutos

turnos de horas

turnos nocturnos

turnos que cruzan medianoche

turnos de varios días

errores de formato

errores de orden

Se ejecuta con:

php tests.php

📄 post_test.php

Pruebas automáticas vía web (WAMP).

Muestra los resultados en una tabla HTML.

Usa la misma lógica de lib_turnos.php.

Permite probar el sistema desde el navegador sin usar CLI.

Acceso:

http://localhost/post_test.php

🧮 mostrarTiempo()

Función auxiliar que convierte horas decimales en:

minutos exactos (si < 1h)

formato HH:MM (si ≥ 1h)

Usada por index.php para mostrar resultados de forma clara.

🚀 Cómo usar el sistema

Inicia WAMP.

Coloca los archivos en www/.

Abre:

http://localhost/index.php

Introduce entrada, salida y horarios de nocturnidad.

Obtén resultados exactos.

🧪 Cómo ejecutar las pruebas

CLI:

php tests.php

Web:

http://localhost/post_test.php

📌 Notas importantes

Toda la lógica está centralizada en lib_turnos.php.

index.php solo muestra resultados.

No hay duplicación de cálculos.

El sistema está preparado para ampliarse (BD, API, etc.).

Nocturnidad funciona incluso en turnos de varios días.

✍️ Créditos

Proyecto desarrollado por:

Jesús — diseño, lógica, pruebas y arquitectura

Copilot — asistencia técnica, refactorización y estructura

Gemini — inició el proyecto y luchó valientemente contra los minutos

