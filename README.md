# Casino

Aplicación PHP ligera que muestra un minijuego de dados sin dependencias externas. Inicia un servidor PHP embebido y juega directamente en el navegador.

## Requisitos
- PHP 8.1 o superior.

## Ejecución
1. Instala las dependencias de PHP (ninguna adicional a PHP).
2. Desde la raíz del proyecto, inicia el servidor embebido:
   ```bash
   php -S localhost:8000
   ```
3. Abre <http://localhost:8000> en el navegador y comienza a jugar.

## Reglas del juego
- Ingresa tu apuesta y presiona **Jugar**.
- Las combinaciones ganadoras multiplican tu apuesta:
  - Trío: x4
  - Par: x2
- Sin coincidencias: pierdes el monto apostado.
- Usa **Reiniciar saldo** para volver al saldo inicial.
