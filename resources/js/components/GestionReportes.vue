<template>
    <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; max-width: 800px; margin: 0 auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;">
            <h2 style="margin: 0; color: #1e293b;">📊 Reportes y Estadísticas</h2>
            <span style="color: #64748b; font-size: 14px;">Módulo de exportación Excel</span>
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
            <!-- <div style="text-align: right; margin-top: 10px;">
                <button 
                    @click="descargarReporte" 
                    :disabled="!form.sede_id || descargando"
                    style="padding: 15px 30px; font-size: 16px; font-weight: bold; color: white; border: none; border-radius: 8px; cursor: pointer; transition: 0.3s;"
                    :style="{ backgroundColor: (!form.sede_id || descargando) ? '#94a3b8' : '#10b981' }"
                >
                    <span v-if="descargando">⏳ Generando Excel...</span>
                    <span v-else>📥 Descargar Reporte</span>
                </button>
            </div> -->

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const sedes = ref([]);
const descargando = ref(false);

// Inicializamos las fechas con el día de hoy por defecto (Formato YYYY-MM-DD)
const hoy = new Date().toISOString().split('T')[0];

const form = ref({
    sede_id: '',
    fecha_inicio: hoy,
    fecha_fin: hoy
});

// 1. Cargar las sedes para el selector
const cargarSedes = async () => {
    try {
        // Asegúrate de que esta ruta devuelva todas las sedes activas para llenar el <select>
        const response = await axios.get('/api/superadmin/sedes-lista'); 
        if (response.data.status === 'ok') {
            sedes.value = response.data.sedes;
        }
    } catch (error) {
        console.error("Error al cargar sedes:", error);
    }
};

// 2. Función para descargar el Excel
// const descargarReporte = async () => {
//     if (form.value.fecha_inicio > form.value.fecha_fin) {
//         Swal.fire('Atención', 'La fecha de inicio no puede ser mayor a la fecha de fin.', 'warning');
//         return;
//     }

//     descargando.value = true;

//     try {
//         // Construimos la URL con los parámetros
//         const url = `/api/reportes/descargar?sede_id=${form.value.sede_id}&fecha_inicio=${form.value.fecha_inicio}&fecha_fin=${form.value.fecha_fin}`;

//         // 🔥 CRÍTICO: Le decimos a Axios que esperamos un archivo binario (Blob), no un JSON
//         const response = await axios.get(url, { responseType: 'blob' });

//         // Truco de Javascript para forzar la descarga del archivo en el navegador
//         const urlArchivo = window.URL.createObjectURL(new Blob([response.data]));
//         const link = document.createElement('a');
//         link.href = urlArchivo;
        
//         // Buscamos el nombre de la sede seleccionada para el nombre del archivo
//         const sedeSeleccionada = sedes.value.find(s => s.id === form.value.sede_id);
//         const nombreLimpio = sedeSeleccionada ? sedeSeleccionada.nombre.replace(/\s+/g, '_') : 'Sede';
        
//         link.setAttribute('download', `Reporte_${nombreLimpio}_${form.value.fecha_inicio}.xlsx`);
        
//         document.body.appendChild(link);
//         link.click();
        
//         // Limpieza
//         document.body.removeChild(link);
//         window.URL.revokeObjectURL(urlArchivo);

//         Swal.fire({
//             toast: true,
//             position: 'top-end',
//             icon: 'success',
//             title: 'Reporte descargado correctamente',
//             showConfirmButton: false,
//             timer: 3000
//         });

//     } catch (error) {
//         console.error("Error descargando reporte", error);
//         Swal.fire('Error', 'Hubo un problema al generar el reporte. Verifica que haya turnos en esas fechas.', 'error');
//     } finally {
//         descargando.value = false;
//     }
// };
// Agregamos el parámetro "formato"
const descargarReporte = async (formato) => {
    if (form.value.fecha_inicio > form.value.fecha_fin) {
        Swal.fire('Atención', 'La fecha de inicio no puede ser mayor a la fecha de fin.', 'warning');
        return;
    }

    descargando.value = true;

    try {
        // 🔥 Inyectamos el parámetro &formato= al final
        const url = `/api/reportes/descargar?sede_id=${form.value.sede_id}&fecha_inicio=${form.value.fecha_inicio}&fecha_fin=${form.value.fecha_fin}&formato=${formato}`;

        const response = await axios.get(url, { responseType: 'blob' });

        const urlArchivo = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = urlArchivo;
        
        const sedeSeleccionada = sedes.value.find(s => s.id === form.value.sede_id);
        const nombreLimpio = sedeSeleccionada ? sedeSeleccionada.nombre.replace(/\s+/g, '_') : 'Sede';
        
        // 🔥 Decidimos la extensión dinámica basada en el formato
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