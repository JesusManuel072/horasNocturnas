# Calculadora de Turnos y Nocturnidad (Backend Matemático)

Una solución web ligera en **PHP 8.4** para el cálculo automatizado de turnos de trabajo y solapes de horas nocturnas mediante un modelo geométrico-matemático continuo (basado en rangos de segundos/minutos), eliminando por completo el uso de estructuras condicionales (`if`) anidadas.

Diseñado para soportar turnos complejos, medianochas y jornadas prolongadas de más de 24 horas (como guardias médicas o turnos especiales).

## 🚀 Características
- **Cero condicionales de tiempo:** Utiliza funciones matemáticas de rango (`max` / `min`) sobre una línea de tiempo continua.
- **Soporta turnos largos (>24h):** Capaz de calcular fichajes que cruzan varios días naturales y múltiples madrugadas.
- **Horquillas de nocturnidad dinámicas:** Permite configurar de manera visual e independiente la hora de inicio y fin de la nocturnidad mediante desplegables.
- **Todo en uno:** Script autónomo listo para pruebas locales en entornos como WAMPP, XAMPP o servidores de producción PHP 8.4+.

## 🛠️ Requisitos
- Servidor web con **PHP 8.4** o superior.

## 📦 Instalación y Uso
1. Clona o descarga este repositorio en tu directorio web local (por ejemplo, `htdocs` en WAMPP).
2. Asegúrate de que el archivo principal sea `index.php`.
3. Abre tu navegador y accede a `http://localhost/tu-proyecto/`.
4. Selecciona las horas de inicio y fin de la nocturnidad, introduce la fecha y hora de entrada y salida del empleado, y haz clic en **Calcular Turno**.

## 📄 Licencia

Este proyecto es de código abierto y se distribuye bajo los términos de la **GNU General Public License v3.0 (GPLv3)**.

Esto significa que eres libre de:
- **Usarlo comercialmente:** Las empresas e instituciones pueden utilizar este software libremente para sus operaciones.
- **Modificarlo:** Puedes adaptar el código a tus necesidades.
- **Distribuirlo:** Puedes compartir copias del programa.

**Condición copyleft:** Si modificas este software o creas una obra derivada y decides **redistribuirla** (ofrecerla públicamente o a terceros), estás obligado a liberar el código fuente modificado bajo esta misma licencia GPLv3, garantizando que el conocimiento siga siendo siempre de y para la comunidad.

Consulta el archivo [LICENSE](LICENSE) para ver los detalles legales completos.