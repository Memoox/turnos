<template>
    <div style="font-family: sans-serif; max-width: 1200px; margin: 0 auto; padding: 10px;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2 style="margin: 0; color: #1e293b; font-size: 24px;">📊 Monitor en Vivo - {{ sedeNombre }}</h2>
                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px;">Supervisión en tiempo real de filas y ventanillas.</p>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; background: #ecfdf5; padding: 8px 15px; border-radius: 20px; border: 1px solid #a7f3d0;">
                <span style="display: inline-block; width: 10px; height: 10px; background: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981; animation: pulse 2s infinite;"></span>
                <span style="color: #065f46; font-size: 13px; font-weight: bold;">Conectado</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid #3b82f6;">
                <h4 style="margin: 0; color: #64748b; font-size: 13px; text-transform: uppercase;">Personas en Espera</h4>
                <p style="margin: 10px 0 0 0; font-size: 32px; font-weight: bold; color: #1e293b;">{{ resumen.enEspera }}</p>
            </div>
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid #10b981;">
                <h4 style="margin: 0; color: #64748b; font-size: 13px; text-transform: uppercase;">Atendidos Hoy</h4>
                <p style="margin: 10px 0 0 0; font-size: 32px; font-weight: bold; color: #1e293b;">{{ resumen.atendidosHoy }}</p>
            </div>
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid #8b5cf6;">
                <h4 style="margin: 0; color: #64748b; font-size: 13px; text-transform: uppercase;">Ventanillas Operando</h4>
                <p style="margin: 10px 0 0 0; font-size: 32px; font-weight: bold; color: #1e293b;">{{ resumen.ventanillasActivas }} <span style="font-size: 16px; color: #94a3b8; font-weight: normal;">de {{ cajas.length }}</span></p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            
            <div>
                <h3 style="margin-top: 0; color: #334155; margin-bottom: 15px;">🖥️ Estado de Ventanillas</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px;">
                    
                    <div v-for="caja in cajas" :key="caja.id" 
                         style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                        
                        <div :style="{ background: caja.cajero ? '#f8fafc' : '#f1f5f9', padding: '12px 15px', borderBottom: '1px solid #e2e8f0', display: 'flex', justifyContent: 'space-between' }">
                            <strong style="color: #334155;">{{ caja.nombre }}</strong>
                            <span v-if="!caja.cajero" style="background: #e2e8f0; color: #64748b; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: bold;">CERRADA</span>
                            <span v-else style="background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: bold;">ABIERTA</span>
                        </div>

                        <div style="padding: 15px; text-align: center;">
                            <div v-if="caja.cajero">
                                <p style="margin: 0; color: #64748b; font-size: 13px;">Cajero:</p>
                                <p style="margin: 0 0 15px 0; font-weight: bold; color: #1e293b;">👤 {{ caja.cajero.name }}</p>
                                
                                <div style="background: #eff6ff; border: 1px dashed #93c5fd; padding: 10px; border-radius: 8px;">
                                    <p style="margin: 0; color: #3b82f6; font-size: 12px; text-transform: uppercase; font-weight: bold;">Turno Actual</p>
                                    <p v-if="caja.turno_actual" style="margin: 5px 0 0 0; font-size: 24px; font-weight: black; color: #1d4ed8;">{{ caja.turno_actual }}</p>
                                    <p v-else style="margin: 5px 0 0 0; font-size: 14px; font-weight: bold; color: #94a3b8;">-- Libre --</p>
                                </div>
                            </div>
                            <div v-else style="padding: 20px 0;">
                                <p style="margin: 0; color: #94a3b8; font-style: italic;">Sin personal asignado</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div>
                <h3 style="margin-top: 0; color: #334155; margin-bottom: 15px;">👥 Fila por Trámite</h3>
                <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                    
                    <div v-for="tramite in fila" :key="tramite.id" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                        <div>
                            <p style="margin: 0; font-weight: bold; color: #334155; font-size: 14px;">{{ tramite.nombre }}</p>
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">Tiempo prom: {{ tramite.tiempoPromedio }}</p>
                        </div>
                        <div style="background: #f1f5f9; color: #475569; font-weight: bold; padding: 5px 12px; border-radius: 20px; font-size: 14px;">
                            {{ tramite.cantidad }}
                        </div>
                    </div>
                    
                    <div v-if="fila.length === 0" style="text-align: center; color: #94a3b8; padding: 20px 0;">
                        No hay fila en este momento.
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// 1. Variables reactivas inicializadas
const sedeNombre = ref('Cargando...');
const resumen = ref({
    enEspera: 0,
    atendidosHoy: 0,
    ventanillasActivas: 0
});
const cajas = ref([]);
const fila = ref([]);
const sedeId = ref(null);
// 2. Función que llama al Backend
const cargarDashboard = async () => {
    try {
        // Asegúrate de que esta URL sea exactamente la misma ruta que usas en tu api.php para adminStats
        const response = await axios.get('/api/dashboard/admin'); 
        
        if (response.data.status === 'ok') {
            sedeId.value = response.data.sede_id;
            sedeNombre.value = response.data.sede_nombre;
            resumen.value = response.data.resumen;
            cajas.value = response.data.cajas;
            fila.value = response.data.fila;
        }
    } catch (error) {
        console.error("Error al cargar el monitor en vivo:", error);
    }
};

onMounted(() => {
    // Al abrir la pantalla, traemos los datos de la base de datos
    cargarDashboard();

    window.Echo.channel('canal-turnos')
        .listen('TurnoGenerado', (evento) => {
            console.log('🔔 Reverb: ¡Alguien sacó un boleto! Actualizando monitor...');
            cargarDashboard(); // Esto refresca las tarjetas al instante
        })
        // Aquí puedes agregar más "timbres" en el futuro
        .listen('TurnoLlamado', (evento) => {
            console.log('🔔 Reverb: ¡Un cajero llamó a alguien! Actualizando monitor...');
            cargarDashboard();
        });
});
</script>

<style scoped>
@keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
</style>