<template>
    <div style="font-family: sans-serif; max-width: 1300px; margin: 40px auto; padding: 20px;">

        <h1 style="color: #0f172a; margin-bottom: 20px;">👑 Panel de Súper Administrador</h1>

        <div style="display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            <button @click="pestanaActual = 'mapa'" :style="btnStyle(pestanaActual === 'mapa')">📍 Mapa en Vivo</button>
            <button @click="pestanaActual = 'sedes'" :style="btnStyle(pestanaActual === 'sedes')">🏢 Sedes</button>
            <button @click="pestanaActual = 'tramites'" :style="btnStyle(pestanaActual === 'tramites')">📑 Trámites</button>
            <button @click="pestanaActual = 'usuarios'" :style="btnStyle(pestanaActual === 'usuarios')">👥 Usuarios</button>
        </div>

        <div v-if="pestanaActual === 'mapa'">
            
            <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; color: #475569;">Monitor Global de Sucursales</h3>
                <span style="background: #e0f2fe; color: #0284c7; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: bold;">
                    🔄 Actualizando cada 2 minutos
                </span>
            </div>

            <div v-if="sedesData.length > 0" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                
                <div v-for="sede in sedesData" :key="sede.id" style="background: white; border: 1px solid #cbd5e1; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                        <h3 style="margin: 0; color: #0f172a; font-size: 18px;">{{ sede.nombre }}</h3>
                        <span style="font-size: 12px; color: #16a34a; font-weight: bold; background: #dcfce7; padding: 4px 8px; border-radius: 10px;">Activa</span>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; text-align: center;">
                        <div style="background: #fef2f2; padding: 10px; border-radius: 8px;">
                            <div style="font-size: 24px; font-weight: black; color: #dc2626;">{{ sede.en_espera }}</div>
                            <div style="font-size: 11px; color: #991b1b; font-weight: bold; text-transform: uppercase;">En Fila</div>
                        </div>
                        
                        <div style="background: #eff6ff; padding: 10px; border-radius: 8px;">
                            <div style="font-size: 24px; font-weight: black; color: #2563eb;">{{ sede.en_ventanilla }}</div>
                            <div style="font-size: 11px; color: #1e40af; font-weight: bold; text-transform: uppercase;">Atendiendo</div>
                        </div>

                        <div style="background: #f0fdf4; padding: 10px; border-radius: 8px;">
                            <div style="font-size: 24px; font-weight: black; color: #16a34a;">{{ sede.finalizados }}</div>
                            <div style="font-size: 11px; color: #166534; font-weight: bold; text-transform: uppercase;">Completados</div>
                        </div>
                    </div>
                </div>

            </div>

            <div v-else style="text-align: center; padding: 60px; color: #64748b; background: white; border-radius: 12px; border: 1px dashed #cbd5e1;">
                <p style="font-size: 18px;">🛰️ Conectando con las sedes del estado o no hay sedes activas...</p>
            </div>
        </div>

        <div v-if="pestanaActual === 'sedes'">
            <GestionSedes />
        </div>
        <div v-if="pestanaActual === 'tramites'">
            <GestionTramites />
        </div>
        <div v-if="pestanaActual === 'usuarios'">
            <GestionAdministradores />
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import GestionSedes from './GestionSedes.vue'; // Asegúrate de haber creado este archivo
import GestionTramites from './GestionTramites.vue';
import GestionAdministradores from './GestionAdministradores.vue';

const pestanaActual = ref('mapa'); // Empezamos en la pestaña de sedes para probar
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
        const response = await axios.get('/api/superadmin/dashboard');
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
    intervaloMapa = setInterval(cargarMapa, 10000); 
});

onUnmounted(() => {
    // Si el Superadmin cierra la pestaña o sale, apagamos el reloj
    if (intervaloMapa) clearInterval(intervaloMapa);
});
</script>