<template>
    <div style="font-family: sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; background-color: #f8fafc; margin: 0;">
        
        <div style="text-align: center; margin-bottom: 40px;" :style="{ opacity: turnoGenerado ? '0.3' : '1', transition: 'opacity 0.3s' }">
            <h1 style="font-size: 40px; color: #1e293b; margin: 0;">Bienvenido</h1>
            <p style="font-size: 20px; color: #64748b; margin-top: 10px;">
                📍 {{ nombreSede || 'Cargando sede...' }}
            </p>
            <h2 style="color: #334155; margin-top: 30px;">¿Qué trámite deseas realizar?</h2>
        </div>

        <div v-if="cargando" style="color: #64748b; font-size: 18px;">
            ⏳ Cargando opciones disponibles...
        </div>

        <div v-else style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; width: 80%; max-width: 800px;"
             :style="{ opacity: turnoGenerado ? '0.3' : '1', pointerEvents: turnoGenerado ? 'none' : 'auto', transition: 'all 0.3s' }">
            
            <button 
                v-for="tipo in tiposTurno" 
                :key="tipo.id"
                @click="solicitarTurno(tipo.id)"
                :disabled="procesando"
                style="padding: 30px 20px; font-size: 22px; font-weight: bold; background-color: #2563eb; color: white; border: none; border-radius: 15px; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.2s;"
                :style="{ opacity: procesando ? '0.7' : '1', transform: procesando ? 'scale(0.98)' : 'none' }"
            >
                <div style="font-size: 40px; margin-bottom: 10px;">{{ tipo.clave }}</div>
                {{ tipo.descripcion }}
            </button>
        </div>

        <div v-if="turnoGenerado" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; background: white; padding: 40px; border-radius: 20px; border: 4px solid #16a34a; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); z-index: 100; min-width: 400px;">
            <p style="font-size: 24px; color: #4b5563; margin: 0; font-weight: bold;">Tu turno es:</p>
            <h1 style="font-size: 100px; color: #16a34a; margin: 10px 0; font-weight: 900;">{{ turnoGenerado }}</h1>
            <p style="color: #475569; font-weight: bold; font-size: 22px;">🖨️ Imprimiendo ticket...</p>
            <p style="color: #64748b; margin-top: 10px; font-size: 18px;">Por favor, tómalo y pasa a la sala de espera.</p>
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
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2'; // Agregamos SweetAlert para errores

const ticketImprimir = ref(null);
const route = useRoute();
const sedeId = route.params.sede_id; 

const nombreSede = ref('');
const tiposTurno = ref([]);
const turnoGenerado = ref(null);
const cargando = ref(true);
const procesando = ref(false); // 🔥 Bandera Anti-Spam

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

const solicitarTurno = async (tipoTurnoId) => {
    // Si ya está procesando una solicitud, ignoramos los siguientes toques
    if (procesando.value) return; 
    
    procesando.value = true;

    try {
        const response = await axios.post('/api/turnos/generar', {
            sede_id: sedeId,
            tipo_turno_id: tipoTurnoId 
        });

        if (response.data.status === 'ok') {
            turnoGenerado.value = response.data.turno.numero_turno; 

            // (Lógica de impresión pendiente)
            
            // Damos un poco más de tiempo (6 segundos) para que lean la pantalla
            setTimeout(() => {
                turnoGenerado.value = null;
                procesando.value = false; // Liberamos el kiosco para el siguiente ciudadano
            }, 6000);
        }
    } catch (error) {
        console.error("Error al generar el turno:", error);
        
        // Reemplazamos el alert nativo por un modal profesional
        Swal.fire({
            icon: 'error',
            title: 'No se pudo generar el turno',
            text: 'Por favor, intente nuevamente o acuda a recepción.',
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Entendido'
        });
        
        procesando.value = false; // Liberamos si hubo error
    }
};

onMounted(() => {
    cargarTiposDeTurno();
});
</script>

<style scoped>
button:hover:not(:disabled) {
    transform: translateY(-5px);
    background-color: #1d4ed8 !important;
}

/* 🔥 MAGIA CSS PARA LA IMPRESIÓN */
@media screen {
    /* En la pantalla normal (iPad/Monitor), el ticket jamás se ve */
    #ticket-termico {
        display: none !important;
    }
}

@media print {
    /* A la hora de imprimir, ocultamos toda la interfaz del kiosco */
    body * {
        visibility: hidden;
    }
    /* Y solo mostramos el bloque del ticket y sus elementos internos */
    #ticket-termico, #ticket-termico * {
        visibility: visible;
    }
    /* Lo posicionamos en la esquina superior izquierda para la impresora térmica */
    #ticket-termico {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>