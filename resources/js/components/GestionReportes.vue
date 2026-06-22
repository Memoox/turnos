<template>
    <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; max-width: 800px; margin: 0 auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;">
            <h2 style="margin: 0; color: #1e293b;">📊 Reportes y Estadísticas</h2>
            <span style="color: #64748b; font-size: 14px;">Módulo de exportación Excel/PDF</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <div>
                <label style="font-weight: bold; color: #475569; display: block; margin-bottom: 8px;">1. Selecciona la Sede:</label>
                <select v-model="form.sede_id" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 16px; outline: none; background-color: #f8fafc;">
                    <option value="" disabled>-- Elige una sede --</option>
                    <option v-for="sede in sedes" :key="sede.id" :value="sede.id">
                        {{ sede.nombre }}
                    </option>
                </select>
            </div>

            <div>
                <label style="font-weight: bold; color: #475569; display: block; margin-bottom: 8px;">Filtros rápidos:</label>
                <div style="display: flex; gap: 10px;">
                    <button @click="setFiltro('hoy')" style="padding: 8px 15px; background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; color: #334155; font-weight: bold; transition: 0.2s;" onmouseover="this.style.backgroundColor='#e2e8f0'" onmouseout="this.style.backgroundColor='#f1f5f9'">
                        📅 Solo Hoy
                    </button>
                    <button @click="setFiltro('ayer')" style="padding: 8px 15px; background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; color: #334155; font-weight: bold; transition: 0.2s;" onmouseover="this.style.backgroundColor='#e2e8f0'" onmouseout="this.style.backgroundColor='#f1f5f9'">
                        ⏪ Solo Ayer
                    </button>
                    <button @click="setFiltro('mes')" style="padding: 8px 15px; background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; color: #334155; font-weight: bold; transition: 0.2s;" onmouseover="this.style.backgroundColor='#e2e8f0'" onmouseout="this.style.backgroundColor='#f1f5f9'">
                        📆 Todo este Mes
                    </button>
                </div>
            </div>

            <div style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label style="font-weight: bold; color: #475569; display: block; margin-bottom: 8px;">2. Fecha de Inicio:</label>
                    <input type="date" v-model="form.fecha_inicio" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 16px; outline: none; background-color: #f8fafc;">
                </div>
                
                <div style="flex: 1;">
                    <label style="font-weight: bold; color: #475569; display: block; margin-bottom: 8px;">3. Fecha de Fin:</label>
                    <input type="date" v-model="form.fecha_fin" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 16px; outline: none; background-color: #f8fafc;">
                </div>
            </div>

            <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; border-radius: 4px; margin-top: 10px;">
                <p style="margin: 0; color: #1e3a8a; font-size: 14px;">
                    💡 <strong>Nota:</strong> El reporte incluirá los promedios de tiempos de atención y la estadística de usuarios atendidos por hora según la Sede seleccionada.
                </p>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button 
                    @click="descargarReporte('excel')" 
                    :disabled="!form.sede_id || descargando"
                    style="padding: 15px 25px; font-size: 16px; font-weight: bold; color: white; border: none; border-radius: 8px; cursor: pointer; transition: 0.3s;"
                    :style="{ backgroundColor: (!form.sede_id || descargando) ? '#94a3b8' : '#10b981' }"
                >
                    <span v-if="descargando">⏳ Generando...</span>
                    <span v-else>📊 Descargar Excel</span>
                </button>

                <button 
                    @click="descargarReporte('pdf')" 
                    :disabled="!form.sede_id || descargando"
                    style="padding: 15px 25px; font-size: 16px; font-weight: bold; color: white; border: none; border-radius: 8px; cursor: pointer; transition: 0.3s;"
                    :style="{ backgroundColor: (!form.sede_id || descargando) ? '#94a3b8' : '#ef4444' }"
                >
                    <span v-if="descargando">⏳ Generando...</span>
                    <span v-else>📄 Descargar PDF</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const sedes = ref([]);
const descargando = ref(false);

const obtenerFechaLocal = (fecha) => {
    const offset = fecha.getTimezoneOffset() * 60000;
    return new Date(fecha.getTime() - offset).toISOString().split('T')[0];
};

const hoy = obtenerFechaLocal(new Date());

const form = ref({
    sede_id: '',
    fecha_inicio: hoy,
    fecha_fin: hoy
});

const setFiltro = (tipo) => {
    const fechaActual = new Date();
    const diaSemana = fechaActual.getDay(); // 0 = Domingo, 6 = Sábado

    if (tipo === 'hoy') {
        if (diaSemana === 0 || diaSemana === 6) {
            Swal.fire({
                icon: 'info',
                title: 'Día inhábil',
                text: 'Hoy es fin de semana. El reporte se generará, pero es probable que no haya turnos registrados a menos que haya personal de guardia.',
                confirmButtonColor: '#3b82f6'
            });
        }
        form.value.fecha_inicio = obtenerFechaLocal(fechaActual);
        form.value.fecha_fin = obtenerFechaLocal(fechaActual);
    } 
    else if (tipo === 'ayer') {
        let diasRestar = 1;
        
        if (diaSemana === 1) { 
            diasRestar = 3; 
        } else if (diaSemana === 0) {
            diasRestar = 2;
        }
        
        fechaActual.setDate(fechaActual.getDate() - diasRestar);
        const diaHabilAnterior = obtenerFechaLocal(fechaActual);
        
        form.value.fecha_inicio = diaHabilAnterior;
        form.value.fecha_fin = diaHabilAnterior;
    } 
    else if (tipo === 'mes') {
        const primerDia = new Date(fechaActual.getFullYear(), fechaActual.getMonth(), 1);
        form.value.fecha_inicio = obtenerFechaLocal(primerDia);
        form.value.fecha_fin = obtenerFechaLocal(new Date()); 
    }
};

const cargarSedes = async () => {
    try {
        const response = await axios.get('/api/superadmin/sedes-lista'); 
        if (response.data.status === 'ok') {
            sedes.value = response.data.sedes;
        }
    } catch (error) {
        console.error("Error al cargar sedes:", error);
    }
};

const descargarReporte = async (formato) => {
    if (form.value.fecha_inicio > form.value.fecha_fin) {
        Swal.fire('Atención', 'La fecha de inicio no puede ser mayor a la fecha de fin.', 'warning');
        return;
    }

    descargando.value = true;

    try {
        const url = `/api/reportes/descargar?sede_id=${form.value.sede_id}&fecha_inicio=${form.value.fecha_inicio}&fecha_fin=${form.value.fecha_fin}&formato=${formato}`;

        const response = await axios.get(url, { responseType: 'blob' });

        const urlArchivo = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = urlArchivo;
        
        const sedeSeleccionada = sedes.value.find(s => s.id === form.value.sede_id);
        const nombreLimpio = sedeSeleccionada ? sedeSeleccionada.nombre.replace(/\s+/g, '_') : 'Sede';
       
        const extension = formato === 'pdf' ? 'pdf' : 'xlsx';
        link.setAttribute('download', `Reporte_${nombreLimpio}_${form.value.fecha_inicio}.${extension}`);
        
        document.body.appendChild(link);
        link.click();
        
        document.body.removeChild(link);
        window.URL.revokeObjectURL(urlArchivo);

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: `Reporte ${formato.toUpperCase()} descargado`,
            showConfirmButton: false,
            timer: 3000
        });

    } catch (error) {
        console.error("Error descargando reporte", error);
        Swal.fire('Error', 'Hubo un problema al generar el reporte.', 'error');
    } finally {
        descargando.value = false;
    }
};

onMounted(() => {
    cargarSedes();
});
</script>