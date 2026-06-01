<template>
    <div style="font-family: sans-serif; max-width: 800px; margin: 50px auto; text-align: center;">

        <div style="margin-bottom: 40px;" v-if="cajero">
            <h1>👤 Panel de Atención</h1>
            <h2 style="color: #1f2937;">
                {{ cajero.caja?.nombre || 'Sin Ventanilla Asignada' }} ({{ cajero.name }})
            </h2>
            <br>
            <p style="color: #64748b; margin-top: -10px; font-weight: bold;">
                📍 Sede: {{ cajero.sede?.nombre || 'Global' }}
            </p>
        </div>
        
        <div style="margin-bottom: 40px;" v-else>
            <p style="color: #6b7280; font-size: 18px;">Cargando perfil del cajero... ⏳</p>
        </div>

        <div style="background: #f3f4f6; padding: 20px; border-radius: 15px; display: inline-block; margin-bottom: 40px; border: 2px solid #e5e7eb;">
            <h3 style="margin: 0; color: #4b5563;">Ciudadanos en Espera</h3>
            <h1 style="font-size: 60px; color: #dc2626; margin: 10px 0;">{{ totalPendientes }}</h1>
        </div>

        <br>

        <button @click="llamarSiguiente" 
                :disabled="!cajero"
                style="padding: 20px 50px; font-size: 24px; font-weight: bold; background-color: #16a34a; color: white; border: none; border-radius: 10px; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            📢 Llamar Siguiente Turno
        </button>

        <div v-if="turnoActual" style="margin-top: 50px; padding-top: 30px; border-top: 2px dashed #ccc;">
            <h3 style="color: #4b5563;">Atendiendo actualmente a:</h3>
            <h1 style="font-size: 80px; color: #2563eb; margin: 10px 0;">{{ turnoActual }}</h1>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

// Guardaremos todo el objeto que devuelva Laravel
const cajero = ref(null); 
const totalPendientes = ref(0);
const turnoActual = ref(null);

// 1. FUNCIÓN PARA IDENTIFICAR AL USUARIO LOGUEADO
const consultarPerfil = async () => {
    try {
        const response = await axios.get('/api/me');
        cajero.value = response.data.user;

        // Si el usuario tiene una sede asignada, disparamos el flujo dinámico
        if (cajero.value && cajero.value.sede_id) {
            actualizarPendientes(cajero.value.sede_id);
            conectarWebSocket(cajero.value.sede_id);
        }
    } catch (error) {
        console.error("Error trayendo los datos de sesión:", error);
        // Si hay error de sesión (cookie vencida o similar), limpiamos y expulsamos
        localStorage.removeItem('user_rol');
        router.push('/login');
    }
};

// 2. FUNCIÓN PARA CONTAR LA FILA (Ahora requiere la Sede por parámetro)
const actualizarPendientes = async (sedeId) => {
    try {
        const response = await axios.get(`/api/turnos/pendientes/${sedeId}`);
        if (response.data.status === 'ok') {
            const conteos = response.data.data;
            totalPendientes.value = Object.values(conteos).reduce((a, b) => a + b, 0);
        }
    } catch (error) {
        console.error("Error al traer la fila:", error);
    }
};

// 3. CONECTAR AL CANAL CORRECTO BASADO EN LA RESPUESTA DE LA API
const conectarWebSocket = (sedeId) => {
    console.log(`📡 Escuchando en tiempo real el canal: field ->` + ` sede.${sedeId}.pendientes`);

    window.Echo.channel(`sede.${sedeId}.pendientes`)
        .listen('TurnoGenerado', (e) => {
            console.log('🎟️ ¡Nuevo boleto detectado en la entrada!');
            actualizarPendientes(sedeId);
        });
};

// 4. BOTÓN LLAMAR SIGUIENTE
const llamarSiguiente = async () => {
    try {
        const response = await axios.post('/api/turnos/atender');

        if (response.data.status === 'ok') {
            turnoActual.value = response.data.turno;
            // Refrescamos usando la sede de nuestro objeto reactivo
            actualizarPendientes(cajero.value.sede_id); 
        } else if (response.data.status === 'no-data') {
            alert('¡Excelente trabajo! Ya no hay turnos en la fila.');
        }
    } catch (error) {
        console.error("Error al atender:", error);
        alert(error.response?.data?.message || "Ocurrió un error al procesar el turno.");
    }
};



onMounted(() => {
    // Al arrancar, lo primero que hacemos es averiguar quién es el usuario
    consultarPerfil();
});

onUnmounted(() => {
    if (cajero.value && cajero.value.sede_id) {
        window.Echo.leave(`sede.${cajero.value.sede_id}.pendientes`);
    }
});
</script>

<style scoped>
button:hover:not(:disabled) {
    filter: brightness(1.1);
    transform: translateY(-2px);
    transition: all 0.2s ease-in-out;
}
button:disabled {
    background-color: #9ca3af !important;
    cursor: not-allowed;
}
</style>