<template>
    <div style="font-family: sans-serif; max-width: 1000px; margin: 40px auto; padding: 20px;">

        <h1 style="color: #0f172a; margin-bottom: 20px;">👑 Panel de Súper Administrador</h1>

        <div style="display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            <button @click="pestanaActual = 'mapa'" :style="btnStyle(pestanaActual === 'mapa')">📍 Mapa en Vivo</button>
            <button @click="pestanaActual = 'sedes'" :style="btnStyle(pestanaActual === 'sedes')">🏢 Sedes</button>
            <button @click="pestanaActual = 'tramites'" :style="btnStyle(pestanaActual === 'tramites')">📑 Trámites</button>
            <button @click="pestanaActual = 'usuarios'" :style="btnStyle(pestanaActual === 'usuarios')">👥 Administradores</button>
        </div>

        <div v-if="pestanaActual === 'mapa'">
            <div v-if="sedesData.length > 0" style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                <div v-for="sede in sedesData" :key="sede.id" style="background: white; border: 1px solid #cbd5e1; padding: 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div>
                        <h3 style="margin: 0; color: #334155;">{{ sede.nombre }}</h3>
                        
                        <span v-if="sede.status" style="font-size: 13px; color: #16a34a; font-weight: bold;">● Operando</span>
                        <span v-else style="font-size: 13px; color: #dc2626; font-weight: bold;">● Inactiva</span>
                        
                        <p style="margin: 8px 0 0; font-size: 14px; color: #64748b;">
                            Fila actual: <strong style="color: #dc2626; font-size: 16px;">{{ sede.turnos_espera || 0 }}</strong> personas
                        </p>
                    </div>
                    <button style="padding: 8px 15px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; color: #334155; font-weight: bold;">Ver Detalle</button>
                </div>
            </div>

            <div v-else style="text-align: center; padding: 40px; color: #64748b;">
                <p>🛰️ Conectando con las sedes del estado...</p>
            </div>
        </div>

        <div v-if="pestanaActual === 'sedes'">
            <GestionSedes />
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import GestionSedes from './GestionSedes.vue'; // Asegúrate de haber creado este archivo

const pestanaActual = ref('sedes'); // Empezamos en la pestaña de sedes para probar
const sedesData = ref([]); 
let intervaloMapa = null; // Guardamos el intervalo para poder apagarlo si salimos

// Estilo dinámico para los botones de las pestañas
const btnStyle = (activo) => ({
    padding: '10px 15px',
    border: 'none',
    background: activo ? '#38bdf8' : 'transparent',
    color: activo ? '#082f49' : '#64748b',
    borderRadius: '6px',
    fontWeight: 'bold',
    cursor: 'pointer'
});

// Tu función original del mapa
const cargarMapa = async () => {
    try {
        const response = await axios.get('/api/dashboard/superadmin');
        if (response.data.status === 'ok') {
            sedesData.value = response.data.sedes;
        }
    } catch (error) {
        console.error("Error cargando el mapa de sedes:", error);
    }
};

onMounted(() => {
    cargarMapa();
    // Consultamos cada 10 segundos
    intervaloMapa = setInterval(cargarMapa, 300000); 
});

onUnmounted(() => {
    // Si el Superadmin cierra la pestaña o sale, apagamos el reloj
    if (intervaloMapa) clearInterval(intervaloMapa);
});
</script>