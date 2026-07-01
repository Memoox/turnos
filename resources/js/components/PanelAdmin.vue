<template>
    
    <div style="font-family: sans-serif; max-width: 1300px; margin: 0 auto;">
        
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
                🖥️ Gestión de Ventanillas
            </button>
            <button 
                @click="pestanaActual = 'reportes'" 
                :style="{ background: pestanaActual === 'reportes' ? '#1e293b' : '#f8fafc', color: pestanaActual === 'reportes' ? 'white' : '#64748b' }"
                style="padding: 10px 20px; border: 1px solid #cbd5e1; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: bold; transition: all 0.2s;">
                📄 Reportes
            </button>
        </div>

        <div v-if="pestanaActual === 'dashboard'">
            <DashboardAdmin ref="monitorComponent" />
        </div>

        <div v-if="pestanaActual === 'personal'">
            <GestionCajeros />
        </div>
        <div v-if="pestanaActual === 'ventanas'">
            <GestionVentanillas />
        </div>
        <div v-if="pestanaActual === 'reportes'">
            <ReporteSede />
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import GestionCajeros from './GestionCajeros.vue';
import GestionVentanillas from './GestionVentanillas.vue';
import DashboardAdmin from './DashboardAdmin.vue'
import ReporteSede from './ReporteSede.vue';

const pestanaActual = ref('dashboard');
const sedeNombre = ref('Cargando...');
const sedeIdActual = ref(null);

const monitorComponent = ref(null);

const obtenerSede = async () => {
    try {
       const response = await axios.get('/api/dashboard/admin'); 
        
        if (response.data.status === 'ok') {
            sedeIdActual.value = response.data.sede_id; 
            conectarWebSocket(sedeIdActual.value);
        }
    } catch (error) {
        console.error("Error obteniendo los datos de la sede:", error);
    }
};

const conectarWebSocket = (sedeId) => {
    // console.log(`📡 Admin escuchando actividad de la sede: ${sedeId}`);

    window.Echo.channel(`sede.${sedeId}.pendientes`)
        .listen('TurnoGenerado', () => {
            // console.log('🎟️ Nuevo turno en recepción. Actualizando números...');

            setTimeout(() => {
                if (monitorComponent.value) {
                    monitorComponent.value.cargarDashboard();
                }
            }, 500);
            
        });
        
    // Escuchamos el canal de la TV por si un cajero atiende a alguien
    window.Echo.channel(`sede.${sedeId}.pantalla`)
        .listen('TurnoLlamado', () => {
            // console.log('🗣️ Turno atendido. Actualizando números...');

            setTimeout(() => {
                if (monitorComponent.value) {
                    monitorComponent.value.cargarDashboard();
                }
            }, 500);
        
        });
};


onMounted(() => {
    obtenerSede();
});

onUnmounted(() => {
    if (sedeIdActual.value) {
        window.Echo.leave(`sede.${sedeIdActual.value}.pendientes`);

        window.Echo.leave(`sede.${sedeIdActual.value}.pantalla`);
    }
});
</script>