<!-- <template>
    <div style="font-family: sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 40px;">
            <h1>📺 Pantalla de Sala de Espera</h1>
            <h2 style="color: gray;">Identificador de Sede: {{ sedeId }}</h2>
        </div>
        
        <div style="display: flex; gap: 40px; justify-content: center; align-items: flex-start;">
            
            <div style="flex: 1; text-align: center; padding: 40px; background: white; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                <p style="font-size: 24px; margin: 0; color: #4b5563; font-weight: bold; letter-spacing: 1px;">LLAMADO ACTUAL</p>
                <h1 style="font-size: 110px; color: #2563eb; margin: 20px 0; font-weight: 800;">{{ turnoActual || '--' }}</h1>
                <h2 style="font-size: 40px; color: #16a34a; margin: 0; font-weight: bold;">{{ cajaAsignada || 'SALA DE ESPERA' }}</h2>
            </div>

            <div style="width: 450px; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                <h3 style="margin-top: 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; color: #1f2937; font-size: 22px;">Últimos Llamados</h3>
                
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 20px;">
                    <thead>
                        <tr style="color: #6b7280; border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 10px 0;">Turno</th>
                            <th style="padding: 10px 0; text-align: right;">Ventanilla</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in historialTurnos" :key="index" style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 14px 0; font-weight: bold; color: #1f2937;">{{ item.turno }}</td>
                            <td style="padding: 14px 0; text-align: right; color: #2563eb; font-weight: bold;">{{ item.caja }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const sedeId = route.params.sede_id; // Captura dinámicamente la sede desde la URL (/tv/1)

const turnoActual = ref(null);
const cajaAsignada = ref(null);
const historialTurnos = ref([]);

// 1. FUNCIÓN PARA CARGAR EL HISTORIAL INICIAL DESDE LA API
const consultarEstadoInicial = async () => {
    try {
        console.log(`Consultando últimos turnos en base de datos para sede ${sedeId}...`);
        const response = await axios.get(`/api/turnos/pantalla/${sedeId}`);
        
        if (response.data.status === 'ok') {
            historialTurnos.value = response.data.turnos;
            
            // Como el controlador devuelve el arreglo ordenado del más reciente al más viejo,
            // tomamos el primer registro con datos reales para inicializar el bloque grande
            const ultimoLlamado = response.data.turnos.find(t => t.turno !== '--');
            if (ultimoLlamado) {
                turnoActual.value = ultimoLlamado.turno;
                cajaAsignada.value = ultimoLlamado.caja;
            }
        } else if (response.data.status === 'no-data') {
            // Rellena la estructura visual con guiones si no hay registros previos hoy
            historialTurnos.value = response.data.turnos;
        }
    } catch (error) {
        console.error("Error cargando los turnos iniciales de la pantalla:", error);
    }
};

onMounted(() => {
    // Primero traemos lo que ya existe en la base de datos para no iniciar en blanco
    consultarEstadoInicial();

    console.log(`📡 Conectando canal de WebSocket dinámico: sede.${sedeId}.pantalla`);

    // Escuchamos el evento de forma 100% dinámica basándonos en la URL
    window.Echo.channel(`sede.${sedeId}.pantalla`)
        .listen('TurnoLlamado', (e) => {
            console.log('🔥 ¡Nuevo turno recibido en tiempo real!', e);
            
            // Actualizamos instantáneamente el recuadro grande principal
            turnoActual.value = e.turno;
            cajaAsignada.value = e.caja;
            
            // Agregamos el nuevo turno al principio de la lista lateral
            historialTurnos.value.unshift({ turno: e.turno, caja: e.caja });
            
            // Removemos el último elemento para mantener estrictamente un tope de 10 renglones
            if (historialTurnos.value.length > 10) {
                historialTurnos.value.pop();
            }
        });
});
</script> -->

<template>
    <div style="font-family: sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto;">
        
        <div v-if="!iniciado" style="height: 80vh; display: flex; justify-content: center; align-items: center; flex-direction: column;">
            <h2 style="color: #4b5563; margin-bottom: 30px;">La pantalla está en pausa para habilitar el sonido</h2>
            <button @click="iniciarPantalla" style="padding: 25px 50px; font-size: 28px; background-color: #2563eb; color: white; border: none; border-radius: 15px; cursor: pointer; box-shadow: 0 10px 15px rgba(0,0,0,0.3); transition: transform 0.2s;">
                ▶️ Iniciar Pantalla y Activar Voz
            </button>
        </div>

        <div v-else>
            <div style="text-align: center; margin-bottom: 40px;">
                <h1>📺 Pantalla de Sala de Espera</h1>
                <h2 style="color: gray;">Identificador de Sede: {{ sedeId }}</h2>
            </div>
            
            <div style="display: flex; gap: 40px; justify-content: center; align-items: flex-start;">
                
                <div style="flex: 1; text-align: center; padding: 40px; background: white; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                    <p style="font-size: 24px; margin: 0; color: #4b5563; font-weight: bold; letter-spacing: 1px;">LLAMADO ACTUAL</p>
                    <h1 style="font-size: 110px; color: #2563eb; margin: 20px 0; font-weight: 800;">{{ turnoActual || '--' }}</h1>
                    <h2 style="font-size: 40px; color: #16a34a; margin: 0; font-weight: bold;">{{ cajaAsignada || 'SALA DE ESPERA' }}</h2>
                </div>

                <div style="width: 450px; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                    <h3 style="margin-top: 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; color: #1f2937; font-size: 22px;">Últimos Llamados</h3>
                    
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 20px;">
                        <thead>
                            <tr style="color: #6b7280; border-bottom: 2px solid #e5e7eb;">
                                <th style="padding: 10px 0;">Turno</th>
                                <th style="padding: 10px 0; text-align: right;">Ventanilla</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in historialTurnos" :key="index" style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 14px 0; font-weight: bold; color: #1f2937;">{{ item.turno }}</td>
                                <td style="padding: 14px 0; text-align: right; color: #2563eb; font-weight: bold;">{{ item.caja }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const sedeId = route.params.sede_id; 

const iniciado = ref(false); // Bandera para mostrar el botón inicial
const turnoActual = ref(null);
const cajaAsignada = ref(null);
const historialTurnos = ref([]);

// 1. DESBLOQUEAR EL AUDIO
const iniciarPantalla = () => {
    iniciado.value = true;
    
    // Truco: Hablamos algo en volumen cero para que el navegador nos dé permiso permanente
    let msg = new SpeechSynthesisUtterance("Listo");
    msg.volume = 0; 
    window.speechSynthesis.speak(msg);

    const audioSilencioso = new Audio('/timbre_aero.mp3');
    audioSilencioso.volume = 0;
    audioSilencioso.play().catch(e => console.log("Audio en pausa hasta interacción"));
};

// 2. FUNCIÓN PARA GENERAR LA VOZ
const anunciarVoz = (turnoTexto, ventanillaTexto) => {
    // Si la API del navegador está leyendo algo más, la cancelamos para que diga esto de inmediato
    window.speechSynthesis.cancel(); 

    // Reemplazamos el guion por un espacio para que diga "D cero cero uno" en lugar de "D guion..."
    const turnoLimpio = turnoTexto.replace('-', ' '); 
    const textoALeer = `Turno ${turnoLimpio}, pasar a ${ventanillaTexto}`;

    let mensaje = new SpeechSynthesisUtterance(textoALeer);
    mensaje.lang = 'es-MX'; // Español de México
    mensaje.rate = 0.85;    // Velocidad un poco más pausada para que se entienda claro
    mensaje.pitch = 1;      // Tono normal

    window.speechSynthesis.speak(mensaje);
};

const reproducirTimbre = () => {
    // Busca el archivo directamente en la carpeta public de Laravel
    const audio = new Audio('/header.mp3'); 
    audio.play().catch(error => console.error("Error al reproducir el timbre:", error));
};

// 3. CARGA DEL HISTORIAL
const consultarEstadoInicial = async () => {
    try {
        const response = await axios.get(`/api/turnos/pantalla/${sedeId}`);
        if (response.data.status === 'ok') {
            historialTurnos.value = response.data.turnos;
            const ultimoLlamado = response.data.turnos.find(t => t.turno !== '--');
            if (ultimoLlamado) {
                turnoActual.value = ultimoLlamado.turno;
                cajaAsignada.value = ultimoLlamado.caja;
            }
        } else if (response.data.status === 'no-data') {
            historialTurnos.value = response.data.turnos;
        }
    } catch (error) {
        console.error("Error cargando los turnos iniciales:", error);
    }
};

onMounted(() => {
    consultarEstadoInicial();

    window.Echo.channel(`sede.${sedeId}.pantalla`)
        .listen('TurnoLlamado', (e) => {
            console.log('🔥 ¡Nuevo turno recibido en tiempo real!', e);
            
            // Actualizamos la pantalla (Usamos e.turno y e.caja porque así los definiste en tu Event de Laravel)
            turnoActual.value = e.turno;
            cajaAsignada.value = e.caja;
            
            // Disparamos la voz robótica
            // anunciarVoz(e.turno, e.caja);

            reproducirTimbre();

            // reproducirTimbre();
            // setTimeout(() => anunciarVoz(e.turno, e.caja), 1500); // Espera 1.5 seg a que suene el timbre
            
            historialTurnos.value.unshift({ turno: e.turno, caja: e.caja });
            if (historialTurnos.value.length > 10) {
                historialTurnos.value.pop();
            }
        });
});

// Limpieza del canal al cerrar la ventana
onUnmounted(() => {
    window.Echo.leave(`sede.${sedeId}.pantalla`);
});
</script>