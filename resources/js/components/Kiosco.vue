<template>
    <div style="font-family: sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; background-color: #f8fafc; margin: 0;">
        
        <div style="text-align: center; margin-bottom: 40px;">
            <h1 style="font-size: 40px; color: #1e293b; margin: 0;">Bienvenido</h1>
            <p style="font-size: 20px; color: #64748b; margin-top: 10px;">
                📍 {{ nombreSede || 'Cargando sede...' }}
            </p>
            <h2 style="color: #334155; margin-top: 30px;">¿Qué trámite deseas realizar?</h2>
        </div>

        <div v-if="cargando" style="color: #64748b; font-size: 18px;">
            ⏳ Cargando opciones disponibles...
        </div>

        <div v-else style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; width: 80%; max-width: 800px;">
            
            <button 
                v-for="tipo in tiposTurno" 
                :key="tipo.id"
                @click="solicitarTurno(tipo.id)"
                style="padding: 30px 20px; font-size: 22px; font-weight: bold; background-color: #2563eb; color: white; border: none; border-radius: 15px; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.2s;"
            >
                <div style="font-size: 40px; margin-bottom: 10px;">{{ tipo.clave }}</div>
                {{ tipo.descripcion }}
            </button>

        </div>

        <!-- <div v-if="turnoGenerado" style="margin-top: 50px; text-align: center; background: white; padding: 30px; border-radius: 15px; border: 2px solid #16a34a; box-shadow: 0 10px 15px rgba(0,0,0,0.1);">
            <p style="font-size: 20px; color: #4b5563; margin: 0;">Tu turno es:</p>
            <h1 style="font-size: 80px; color: #16a34a; margin: 10px 0;">{{ turnoGenerado }}</h1>
            <p style="color: #64748b;">Por favor, toma asiento y espera a ser llamado.</p>
        </div> -->
        <div v-if="turnoGenerado" style="margin-top: 50px; text-align: center; background: white; padding: 30px; border-radius: 15px; border: 2px solid #16a34a; box-shadow: 0 10px 15px rgba(0,0,0,0.1);">
            <p style="font-size: 20px; color: #4b5563; margin: 0;">Tu turno es:</p>
            <h1 style="font-size: 80px; color: #16a34a; margin: 10px 0;">{{ turnoGenerado }}</h1>
            <p style="color: #64748b; font-weight: bold; font-size: 18px;">🖨️ Imprimiendo ticket...</p>
            <p style="color: #64748b; margin-top: 5px;">Por favor, tómalo y pasa a la sala de espera.</p>
        </div>

        <div v-if="ticketImprimir" id="ticket-termico">
        <div style="text-align: center; font-family: 'Courier New', Courier, monospace; color: black; line-height: 1.2;">
            <h2 style="margin: 0; font-size: 16px; font-weight: bold;">PODER JUDICIAL</h2>
            <p style="margin: 2px 0; font-size: 12px;">{{ ticketImprimir.sede }}</p>
            <p style="margin: 0; font-size: 11px;">--------------------------------</p>
            
            <p style="margin: 10px 0 5px 0; font-size: 13px; font-weight: bold; text-transform: uppercase;">
                {{ ticketImprimir.tramite_descripcion }}
            </p>
            
            <p style="margin: 0; font-size: 11px;">SU TURNO ES:</p>
            <h1 style="margin: 5px 0; font-size: 42px; font-weight: 900; letter-spacing: 1px;">
                {{ ticketImprimir.turno }}
            </h1>
            
            <p style="margin: 0; font-size: 11px;">--------------------------------</p>
            <p style="margin: 5px 0 0 0; font-size: 11px;">Por favor, espere su llamado</p>
            <p style="margin: 2px 0; font-size: 11px;">en la sala de espera.</p>
            
            <p style="margin: 15px 0 0 0; font-size: 10px; color: #555;">
                {{ ticketImprimir.fecha_hora }}
            </p>
        </div>
    </div>

    </div>

</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const ticketImprimir = ref(null);
const route = useRoute();
const sedeId = route.params.sede_id; // Tomamos el ID de la URL (/kiosco/1)

const nombreSede = ref('');
const tiposTurno = ref([]);
const turnoGenerado = ref(null);
const cargando = ref(true);

// 1. CARGAR LOS TRÁMITES AL INICIAR
const cargarTiposDeTurno = async () => {
    try {
        const response = await axios.get(`/api/sedes/${sedeId}/tipos-turno`);
        if (response.data.status === 'ok') {
            nombreSede.value = response.data.sede;
            tiposTurno.value = response.data.tipos_turno;
        }
    } catch (error) {
        console.error("Error al cargar los trámites:", error);
    } finally {
        cargando.value = false;
    }
};

// 2. ENVIAR LA SOLICITUD AL BACKEND (Ahora mandamos el tipo de trámite)
const solicitarTurno = async (tipoTurnoId) => {
    try {
        const response = await axios.post('/api/turnos/generar', {
            sede_id: sedeId,
            tipo_turno_id: tipoTurnoId // <-- ¡Este es el nuevo dato crucial!
        });

        if (response.data.status === 'ok') {
            turnoGenerado.value = response.data.turno.numero_turno; // ej: "D-001"

            ticketImprimir.value = response.data.data_ticket;
            await nextTick();
            window.print();
            ticketImprimir.value = null;
            
            // Ocultamos el mensaje después de 5 segundos
            setTimeout(() => {
                turnoGenerado.value = null;
            }, 5000);
        }
    } catch (error) {
        console.error("Error al generar el turno:", error);
        alert("Ocurrió un error al generar el turno. Intente nuevamente.");
    }
};

onMounted(() => {
    cargarTiposDeTurno();
});
</script>

<style scoped>
button:hover {
    transform: translateY(-5px);
    background-color: #1d4ed8 !important;
}
</style>