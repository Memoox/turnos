<template>
    <div style="font-family: sans-serif; max-width: 1000px; margin: 40px auto; padding: 20px;">

        <h2 style="color: #1e293b;">📍 Estado de las Sedes</h2>
        
        <div v-if="sedes.length > 0" style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
            
            <div v-for="sede in sedes" :key="sede.id" 
                 style="background: white; border: 1px solid #cbd5e1; padding: 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div>
                    <h3 style="margin: 0; color: #334155;">{{ sede.nombre }}</h3>
                    
                    <span v-if="sede.status" style="font-size: 13px; color: #16a34a; font-weight: bold;">● Operando</span>
                    <span v-else style="font-size: 13px; color: #dc2626; font-weight: bold;">● Inactiva</span>
                    
                    <p style="margin: 8px 0 0; font-size: 14px; color: #64748b;">
                        Fila actual: <strong style="color: #dc2626; font-size: 16px;">{{ sede.turnos_espera }}</strong> personas
                    </p>
                </div>
                <button style="padding: 8px 15px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; color: #334155; font-weight: bold;">Ver Detalle</button>
            </div>

        </div>

        <div v-else style="text-align: center; padding: 40px; color: #64748b;">
            <p>🛰️ Conectando con las sedes del estado...</p>
        </div>

        <div style="margin-top: 40px; background: #1e293b; color: white; padding: 30px; border-radius: 15px;">
            <h3 style="margin-top: 0;">🛠️ Herramientas Maestras</h3>
            <div style="display: flex; gap: 15px;">
                <button style="padding: 12px; background: #38bdf8; color: #082f49; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">➕ Nueva Sede</button>
                <button style="padding: 12px; background: #38bdf8; color: #082f49; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">📑 Tipos de Turno</button>
                <button style="padding: 12px; background: #38bdf8; color: #082f49; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">⚙️ Logs del Sistema</button>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const sedes = ref([]);

const cargarSedes = async () => {
    try {
        const response = await axios.get('/api/dashboard/superadmin');
        if (response.data.status === 'ok') {
            sedes.value = response.data.sedes;
            // Opcional: Podríamos conectar WebSockets aquí para TODAS las sedes,
            // pero para no saturar al Superadmin, dejaremos que recargue o
            // programaremos un setInterval para que consulte cada X segundos.
        }
    } catch (error) {
        console.error("Error cargando el mapa de sedes:", error);
    }
};



onMounted(() => {
    cargarSedes();
    // Truco: Hacemos que consulte silenciosamente cada 10 segundos 
    // para mantener los números actualizados sin usar WebSockets masivos
    setInterval(cargarSedes, 10000); 
});
</script>