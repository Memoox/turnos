#!/bin/bash

echo "🚀 Iniciando el entorno del Sistema de Turnos..."

# 1. Levantar la base de datos en Docker (En segundo plano)
echo "🐳 Levantando contenedor de MySQL..."
docker compose up -d

# 2. Iniciar el servidor de Laravel
echo "🐘 Iniciando servidor PHP (Laravel)..."
php artisan serve &
PID_LARAVEL=$!

# 3. Iniciar Vite (Frontend)
echo "⚡ Iniciando compilador de Vite..."
npm run dev &
PID_VITE=$!

# 4. Iniciar WebSockets (Puerto 6002)
echo "📡 Iniciando servidor de WebSockets..."
#php artisan websockets:serve &
php artisan reverb:start &  
PID_WS=$!

# 5. (Opcional) Iniciar el procesador de colas
echo "⚙️ Iniciando Queue Worker..."
php artisan queue:work &
PID_QUEUE=$!

echo "=================================================="
echo "✅ ¡Todo el entorno está corriendo en esta terminal!"
echo "🛑 Presiona [Ctrl + C] para apagar todos los servicios."
echo "=================================================="

# Función de limpieza: Se ejecuta automáticamente al presionar Ctrl+C
cleanup() {
    echo ""
    echo "🛑 Apagando servicios de Laravel, Vite y WebSockets..."
    kill $PID_LARAVEL $PID_VITE $PID_WS $PID_QUEUE 2>/dev/null
    
    # Opcional: Descomenta la siguiente línea si también quieres que Docker se apague al salir
    docker compose down    
    echo "👋 Entorno apagado correctamente. ¡Nos vemos!"
    exit
}

# Atrapar la señal de interrupción (Ctrl+C) y mandar a llamar a la función cleanup
trap cleanup SIGINT

# Esperar infinitamente para que el script no se cierre y mantenga los procesos vivos
wait
