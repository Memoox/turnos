<template>
    <div style="font-family: sans-serif; max-width: 1000px; margin: 40px auto; padding: 20px;">
        
        <div v-if="stats" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px;">
            <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center;">
                <h3 style="color: #64748b; margin-top: 0;">Ventanillas Activas</h3>
                <h1 style="color: #2563eb; font-size: 40px; margin: 10px 0;">{{ stats.ventanillas_activas }}</h1>
            </div>
            <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center;">
                <h3 style="color: #64748b; margin-top: 0;">Turnos en Espera</h3>
                <h1 style="color: #dc2626; font-size: 40px; margin: 10px 0;">{{ stats.turnos_espera }}</h1>
            </div>
            <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center;">
                <h3 style="color: #64748b; margin-top: 0;">Turnos Totales Hoy</h3>
                <h1 style="color: #ca8a04; font-size: 40px; margin: 10px 0;">{{ stats.turnos_hoy }}</h1>
            </div>
        </div>
        
        <div v-else style="text-align: center; padding: 40px; color: #64748b;">
            <p>📊 Calculando métricas en tiempo real...</p>
        </div>

        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px;">
            <h2 style="margin-top: 0; color: #1e293b;">⚡ Gestión Local</h2>
            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <button style="padding: 15px; background: #334155; color: white; border: none; border-radius: 8px; cursor: pointer; flex: 1;">👥 Gestionar Cajeros</button>
                <button style="padding: 15px; background: #334155; color: white; border: none; border-radius: 8px; cursor: pointer; flex: 1;">🖨️ Configurar Pantallas</button>
                <button style="padding: 15px; background: #334155; color: white; border: none; border-radius: 8px; cursor: pointer; flex: 1;">📊 Reporte Diario</button>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const sedeNombre = ref('Cargando...');
const stats = ref(null);
const sedeIdActual = ref(null);

const cargarEstadisticas = async () => {
    try {
        const response = await axios.get('/api/dashboard/admin');
        if (response.data.status === 'ok') {
            sedeNombre.value = response.data.sede_nombre;
            stats.value = response.data.stats;
            
            // Si es la primera vez que cargamos, conectamos el WebSocket
            if (!sedeIdActual.value) {
                sedeIdActual.value = response.data.sede_id;
                conectarWebSocket(sedeIdActual.value);
            }
        }
    } catch (error) {
        console.error("Error cargando estadísticas:", error);
    }
};

const conectarWebSocket = (sedeId) => {
    console.log(`📡 Admin escuchando actividad de la sede: ${sedeId}`);

    // Escuchamos el mismo canal de la fila que usa el Cajero
    window.Echo.channel(`sede.${sedeId}.pendientes`)
        .listen('TurnoGenerado', () => {
            console.log('🎟️ Nuevo turno en recepción. Actualizando números...');
            cargarEstadisticas(); // Recargamos los números en automático
        });
        
    // Escuchamos el canal de la TV por si un cajero atiende a alguien
    window.Echo.channel(`sede.${sedeId}.pantalla`)
        .listen('TurnoLlamado', () => {
            console.log('🗣️ Turno atendido. Actualizando números...');
            cargarEstadisticas(); // Recargamos los números en automático
        });
};

const cerrarSesion = async () => {
    if (sedeIdActual.value) {
        window.Echo.leave(`sede.${sedeIdActual.value}.pendientes`);
        window.Echo.leave(`sede.${sedeIdActual.value}.pantalla`);
    }
    await axios.post('/api/logout');
    localStorage.removeItem('user_rol');
    router.push('/login');
};

onMounted(() => {
    cargarEstadisticas();
});

onUnmounted(() => {
    if (sedeIdActual.value) {
        // 1. Apagamos el radar de la fila (Kiosco)
        window.Echo.leave(`sede.${sedeIdActual.value}.pendientes`);
        
        // 2. Apagamos el radar de las ventanillas (TV)
        window.Echo.leave(`sede.${sedeIdActual.value}.pantalla`);
    }
});
</script>