<template>
    <div style="font-family: sans-serif; min-height: 100vh; width: 100vw; box-sizing: border-box; background-color: #f8fafc; margin: 0; overflow: hidden; display: flex; flex-direction: column;">
        
        <div v-if="!iniciado" style="flex: 1; display: flex; justify-content: center; align-items: center; flex-direction: column;">
            <h2 style="color: #4b5563; margin-bottom: 30px;">La pantalla está en pausa para habilitar el sonido</h2>
            <button @click="iniciarPantalla" style="padding: 25px 50px; font-size: 28px; background-color: #2563eb; color: white; border: none; border-radius: 15px; cursor: pointer; box-shadow: 0 10px 15px rgba(0,0,0,0.3); transition: transform 0.2s;">
                ▶️ Iniciar Pantalla y Activar Voz
            </button>
        </div>

        <div v-else style="flex: 1; display: flex; flex-direction: column; height: 100vh;">
            
            <header style="background: linear-gradient(90deg, #1e293b 0%, #0f172a 100%); padding: 25px 50px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10;">
                <div>
                    <h1 style="margin: 0; color: white; font-size: 36px; font-weight: bold; letter-spacing: 0.5px;"> BIENVENIDO</h1>
                    <p style="color: #cbd5e1; margin: 5px 0 0 0; font-size: 20px; letter-spacing: 0.5px;">
                        📍 {{ sedeNombre || 'Cargando información...' }}
                    </p>
                </div>
                <div style="text-align: right;">
                    <h1 style="margin: 0; font-size: 46px; color: #ffffff; font-weight: 900; letter-spacing: 1px;">{{ horaActual }}</h1>
                </div>
            </header>
            
            <main style="flex: 1; display: flex; gap: 40px; align-items: stretch; padding: 40px; box-sizing: border-box;">
                
                <div :class="{ 'animacion-destello': animando }" style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; background: white; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 2px solid #e2e8f0; padding: 40px; transition: all 0.3s ease;">
                    <p style="font-size: 32px; margin: 0; color: #64748b; font-weight: bold; letter-spacing: 2px;">LLAMADO ACTUAL</p>
                    
                    <h1 style="font-size: 160px; color: #2563eb; margin: 20px 0; font-weight: 900; line-height: 1;">{{ turnoActual || '--' }}</h1>
                    
                    <div style="background-color: #f0fdf4; padding: 20px 40px; border-radius: 15px; border: 2px solid #bbf7d0; width: 80%; text-align: center;">
                        <p style="font-size: 24px; color: #166534; margin: 0 0 10px 0; font-weight: bold;">Pasar a:</p>
                        <h2 style="font-size: 55px; color: #16a34a; margin: 0; font-weight: 900;">{{ cajaAsignada || 'SALA DE ESPERA' }}</h2>
                    </div>
                </div>

                <div style="width: 35%; min-width: 500px; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 2px solid #e2e8f0; display: flex; flex-direction: column;">
                    <h3 style="margin-top: 0; border-bottom: 3px solid #e2e8f0; padding-bottom: 15px; color: #1e293b; font-size: 32px; text-align: center;">Últimos Llamados</h3>
                    
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 28px;">
                        <thead>
                            <tr style="color: #64748b; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 15px 10px;">Turno</th>
                                <th style="padding: 15px 10px; text-align: right;">Ventanilla</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in historialTurnos" :key="index" :style="{ backgroundColor: index === 0 ? '#eff6ff' : 'transparent', borderBottom: '1px solid #f1f5f9' }">
                                <td style="padding: 22px 10px; font-weight: bold; color: #1e293b;">{{ item.turno }}</td>
                                <td style="padding: 22px 10px; text-align: right; color: #2563eb; font-weight: bold;">{{ item.caja }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const sedeId = route.params.sede_id; 

const iniciado = ref(false); 
const turnoActual = ref(null);
const cajaAsignada = ref(null);
const historialTurnos = ref([]);
const animando = ref(false); 

const sedeNombre = ref(''); 
const horaActual = ref('');
let intervaloReloj;

const actualizarReloj = () => {
    const ahora = new Date();
    horaActual.value = ahora.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
};

const iniciarPantalla = () => {
    iniciado.value = true;
    
    let msg = new SpeechSynthesisUtterance("Listo");
    msg.volume = 0; 
    window.speechSynthesis.speak(msg);

    const audioSilencioso = new Audio('/timbre_aero.mp3');
    audioSilencioso.volume = 0;
    audioSilencioso.play().catch(e => console.log("Audio en pausa hasta interacción"));
};

const anunciarVoz = (turnoTexto, ventanillaTexto) => {
    window.speechSynthesis.cancel(); 

    const turnoLimpio = turnoTexto.replace('-', ' '); 
    const textoALeer = `Turno ${turnoLimpio}, pasar a ${ventanillaTexto}`;

    let mensaje = new SpeechSynthesisUtterance(textoALeer);
    mensaje.lang = 'es-MX'; 
    mensaje.rate = 0.85;    
    mensaje.pitch = 1;      

    window.speechSynthesis.speak(mensaje);
};

const reproducirTimbre = () => {
    const audio = new Audio('/header.mp3'); 
    audio.play().catch(error => console.error("Error al reproducir el timbre:", error));
};

const consultarEstadoInicial = async () => {
    try {
        const response = await axios.get(`/api/turnos/pantalla/${sedeId}`);
        if (response.data.status === 'ok') {
            sedeNombre.value = response.data.sede_nombre;
            historialTurnos.value = response.data.turnos;
            
            const ultimoLlamado = response.data.turnos.find(t => t.turno !== '--');
            if (ultimoLlamado) {
                turnoActual.value = ultimoLlamado.turno;
                cajaAsignada.value = ultimoLlamado.caja;
            }
        } else if (response.data.status === 'no-data') {
            sedeNombre.value = response.data.sede_nombre;
            historialTurnos.value = response.data.turnos;
        }
    } catch (error) {
        console.error("Error cargando los turnos iniciales:", error);
    }
};

onMounted(() => {
    consultarEstadoInicial();
    actualizarReloj();
    intervaloReloj = setInterval(actualizarReloj, 1000); 

    window.Echo.channel(`sede.${sedeId}.pantalla`)
        .listen('TurnoLlamado', (e) => {
            console.log('🔥 ¡Nuevo turno recibido en tiempo real!', e);
            
            turnoActual.value = e.turno;
            cajaAsignada.value = e.caja;
            
            animando.value = true;
            setTimeout(() => animando.value = false, 2000); 
            
            reproducirTimbre();
            setTimeout(() => {
                anunciarVoz(e.turno, e.caja);
            }, 1200); 
            
            historialTurnos.value.unshift({ turno: e.turno, caja: e.caja });
            if (historialTurnos.value.length > 8) { 
                historialTurnos.value.pop();
            }
        });
});

onUnmounted(() => {
    window.Echo.leave(`sede.${sedeId}.pantalla`);
    clearInterval(intervaloReloj);
});
</script>

<style>
body {
    margin: 0;
    padding: 0;
}

.animacion-destello {
    animation: flash 2s ease-out;
}

@keyframes flash {
    0% { background-color: white; border-color: #e2e8f0; }
    15% { background-color: #fef08a; border-color: #eab308; transform: scale(1.02); } 
    100% { background-color: white; border-color: #e2e8f0; transform: scale(1); }
}
</style>