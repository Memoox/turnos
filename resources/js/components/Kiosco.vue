<template>
    <div style="font-family: sans-serif; display: flex; flex-direction: column; height: 100vh; background-color: #f8fafc; margin: 0;">
        
        <header style="background: linear-gradient(90deg, #1e293b 0%, #0f172a 100%); padding: 30px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.15); z-index: 10;">
            <h1 style="margin: 0; color: white; font-size: 36px; font-weight: bold;">Bienvenido</h1>
            <p style="color: #cbd5e1; margin: 10px 0 0 0; font-size: 18px; letter-spacing: 0.5px;">
                📍 {{ nombreSede || 'Cargando sede...' }}
            </p>
        </header>

        <main style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; position: relative;">
            
            <h2 style="color: #475569; margin-bottom: 40px; font-size: 28px; font-weight: normal;" :style="{ opacity: turnoGenerado ? '0.3' : '1', transition: 'opacity 0.3s' }">
                ¿Qué trámite deseas realizar?
            </h2>

            <div v-if="cargando" style="color: #64748b; font-size: 20px; font-weight: bold;">
                <span class="spinner-simple">⏳</span> Cargando opciones disponibles...
            </div>

            <div v-else style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; width: 100%; max-width: 1100px;"
                 :style="{ opacity: turnoGenerado ? '0.3' : '1', pointerEvents: turnoGenerado ? 'none' : 'auto', transition: 'all 0.3s' }">
                
                <button 
                    v-for="tipo in tiposTurno" 
                    :key="tipo.id"
                    @click="solicitarTurno(tipo.id)"
                    :disabled="procesando"
                    class="btn-kiosco"
                    :style="{ opacity: procesando ? '0.7' : '1' }"
                >
                    <div style="font-size: 55px; font-weight: 900; margin-bottom: 5px;">{{ tipo.clave }}</div>
                    
                    <div style="display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 22px; font-weight: 600;">
                        <span>{{ tipo.icono || '🔸' }}</span>
                        {{ tipo.descripcion }}
                    </div>
                </button>

            </div>

            <div v-if="turnoGenerado" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; background: white; padding: 50px; border-radius: 25px; border: 5px solid #16a34a; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); z-index: 100; min-width: 450px;">
                <p style="font-size: 28px; color: #4b5563; margin: 0; font-weight: bold;">Tu turno es:</p>
                <h1 style="font-size: 120px; color: #16a34a; margin: 15px 0; font-weight: 900; line-height: 1;">{{ turnoGenerado }}</h1>
                <p style="color: #475569; font-weight: bold; font-size: 24px;">🖨️ Imprimiendo ticket...</p>
                <p style="color: #64748b; margin-top: 15px; font-size: 20px;">Por favor, tómalo y pasa a la sala de espera.</p>
            </div>
        </main>

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
import Swal from 'sweetalert2'; 

const ticketImprimir = ref(null);
const route = useRoute();
const sedeId = route.params.sede_id; 

const nombreSede = ref('');
const tiposTurno = ref([]);
const turnoGenerado = ref(null);
const cargando = ref(true);
const procesando = ref(false);

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
    if (procesando.value) return; 
    
    procesando.value = true;

    try {
        const response = await axios.post('/api/turnos/generar', {
            sede_id: sedeId,
            tipo_turno_id: tipoTurnoId 
        });

        if (response.data.status === 'ok') {
            turnoGenerado.value = response.data.turno.numero_turno; 

            setTimeout(() => {
                turnoGenerado.value = null;
                procesando.value = false; 
            }, 6000);
        }
    } catch (error) {
        console.error("Error al generar el turno:", error);

        Swal.fire({
            icon: 'error',
            title: 'No se pudo generar el turno',
            text: 'Por favor, intente nuevamente o acuda a recepción.',
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Entendido'
        });
        
        procesando.value = false;
    }
};

onMounted(() => {
    cargarTiposDeTurno();
});
</script>

<style scoped>
/* 🔥 ESTILOS PREMIUM PARA LOS BOTONES DEL KIOSCO */
.btn-kiosco {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
    border-radius: 20px;
    padding: 35px 20px;
    cursor: pointer;
    box-shadow: 0 10px 20px rgba(37, 99, 235, 0.25);
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* Efecto de presión física para pantallas táctiles */
.btn-kiosco:active:not(:disabled) {
    transform: scale(0.96);
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

/* Evitar que hover estorbe en pantallas táctiles, pero mantenerlo bonito por si usan mouse */
@media (hover: hover) {
    .btn-kiosco:hover:not(:disabled) {
        transform: translateY(-4px);
        box-shadow: 0 15px 25px rgba(37, 99, 235, 0.35);
    }
}

/* Animación simple para el relojito de carga */
.spinner-simple {
    display: inline-block;
    animation: spin 2s linear infinite;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

/* CSS PARA LA IMPRESIÓN TÉRMICA */
@media screen {
    #ticket-termico {
        display: none !important;
    }
}

@media print {
    body * {
        visibility: hidden;
    }
    #ticket-termico, #ticket-termico * {
        visibility: visible;
    }
    #ticket-termico {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>