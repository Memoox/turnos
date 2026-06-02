<template>
    <!-- <div style="font-family: sans-serif; max-width: 1200px; margin: 0 auto;">
        
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

    </div> -->
    <div style="font-family: sans-serif; max-width: 1200px; margin: 0 auto;">
        
        <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            <button 
                @click="pestanaActual = 'dashboard'" 
                :style="{ background: pestanaActual === 'dashboard' ? '#1e293b' : '#f8fafc', color: pestanaActual === 'dashboard' ? 'white' : '#64748b' }"
                style="padding: 10px 20px; border: 1px solid #cbd5e1; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: bold; transition: all 0.2s;">
                📊 Dashboard en Vivo
            </button>
            <button 
                @click="pestanaActual = 'personal'" 
                :style="{ background: pestanaActual === 'personal' ? '#1e293b' : '#f8fafc', color: pestanaActual === 'personal' ? 'white' : '#64748b' }"
                style="padding: 10px 20px; border: 1px solid #cbd5e1; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: bold; transition: all 0.2s;">
                👥 Gestión de Cajeros
            </button>
            <button 
                @click="pestanaActual = 'ventanas'" 
                :style="{ background: pestanaActual === 'ventanas' ? '#1e293b' : '#f8fafc', color: pestanaActual === 'ventanas' ? 'white' : '#64748b' }"
                style="padding: 10px 20px; border: 1px solid #cbd5e1; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: bold; transition: all 0.2s;">
                👥 Gestión de Ventanillas
            </button>
        </div>

        <div v-if="pestanaActual === 'dashboard'">
            
        </div>

        <div v-if="pestanaActual === 'personal'">
            <GestionCajeros />
        </div>
        <div v-if="pestanaActual === 'ventanas'">
            <GestionVentanillas />
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import GestionCajeros from './GestionCajeros.vue';
import GestionVentanillas from './GestionVentanillas.vue';

const pestanaActual = ref('dashboard');
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