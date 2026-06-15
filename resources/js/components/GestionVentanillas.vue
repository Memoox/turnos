<template>
    <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h2 style="margin: 0; color: #1e293b; white-space: nowrap;">🏢 Gestión de Ventanillas</h2>
            
            <div style="flex: 1; max-width: 500px;">
                <input 
                    v-model="buscador" 
                    @input="buscarConPausa" 
                    type="text" 
                    placeholder="🔍 Buscar por nombre..." 
                    style="width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; outline: none;"
                >
            </div>

            <button @click="abrirModalNuevo" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; white-space: nowrap;">
                + Nueva Ventanilla
            </button>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; color: #64748b; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px; width: 10%;">ID</th>
                    <th style="padding: 12px; width: 30%;">Nombre</th>
                    <th style="padding: 12px; width: 40%;">Trámites Asignados</th>
                    <th style="padding: 12px; text-align: right; width: 20%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="caja in cajas" :key="caja.id" 
                    :style="{ 
                        borderBottom: '1px solid #f1f5f9',
                        opacity: caja.is_active ? '1' : '0.4', 
                        backgroundColor: caja.is_active ? 'transparent' : '#f8fafc' 
                    }">
                    <td style="padding: 12px; color: #64748b;">#{{ caja.id }}</td>
                    <td style="padding: 12px; font-weight: bold; color: #334155;">
                        {{ caja.nombre }}
                        <span v-if="!caja.is_active" style="margin-left: 8px; font-size: 11px; background: #cbd5e1; color: white; padding: 2px 6px; border-radius: 4px;">Inactiva</span>
                    </td>
                    <td style="padding: 12px;">
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <span v-for="tipo in caja.tipos_de_turno" :key="tipo.id" 
                                  style="background: #e0e7ff; color: #4f46e5; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: bold;">
                                {{ tipo.descripcion }}
                            </span>
                            <span v-if="!caja.tipos_de_turno || caja.tipos_de_turno.length === 0" style="color: #94a3b8; font-size: 13px;">
                                Sin trámites asignados
                            </span>
                        </div>
                    </td>
                    <td style="padding: 12px; text-align: right; white-space: nowrap; width: 1%;">
                        <button v-if="caja.is_active" @click="editarCaja(caja)" style="background: #f59e0b; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; margin-right: 8px; font-weight: bold;">✏️ Editar</button>
                        
                        <button v-if="caja.is_active" @click="cambiarEstado(caja)" style="background: #ef4444; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">❌ Baja</button>
                        <button v-else @click="cambiarEstado(caja)" style="background: #10b981; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">✅ Reactivar</button>
                    </td>
                </tr>
                <tr v-if="cajas.length === 0">
                    <td colspan="4" style="text-align: center; padding: 30px; color: #94a3b8; font-size: 16px;">
                        No se encontraron ventanillas registradas.
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="totalRegistros > 0" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-top: 1px solid #e2e8f0; background: #f8fafc; border-radius: 0 0 12px 12px; margin-top: 10px;">
            <span style="color: #64748b; font-size: 14px;">Total: <strong>{{ totalRegistros }}</strong> registros</span>
            
            <div v-if="totalPaginas > 1" style="display: flex; gap: 8px; align-items: center;">
                <button :disabled="paginaActual === 1" @click="cargarDatos(paginaActual - 1)" 
                    style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: bold; cursor: pointer; transition: 0.2s;"
                    :style="{ background: paginaActual === 1 ? '#f1f5f9' : 'white', color: paginaActual === 1 ? '#94a3b8' : '#334155' }">
                    ⬅️ Anterior
                </button>
                <span style="padding: 0 10px; font-weight: bold; color: #3b82f6;">Pág {{ paginaActual }} de {{ totalPaginas }}</span>
                <button :disabled="paginaActual === totalPaginas" @click="cargarDatos(paginaActual + 1)" 
                    style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: bold; cursor: pointer; transition: 0.2s;"
                    :style="{ background: paginaActual === totalPaginas ? '#f1f5f9' : 'white', color: paginaActual === totalPaginas ? '#94a3b8' : '#334155' }">
                    Siguiente ➡️
                </button>
            </div>
        </div>

        <div v-if="mostrarModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 50;">
            <div style="background: white; padding: 30px; border-radius: 12px; width: 450px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; margin-bottom: 20px; color: #1e293b;">{{ modoEdicion ? '✏️ Editar Ventanilla' : '✨ Nueva Ventanilla' }}</h3>
                
                <form @submit.prevent="guardarCaja" style="display: flex; flex-direction: column; gap: 15px;">
                    <div>
                        <label style="font-size: 14px; font-weight: bold; color: #475569;">Nombre de la Ventanilla:</label>
                        <input v-model="form.nombre" type="text" placeholder="Ej. Ventanilla 1" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; outline: none;">
                    </div>

                    <div>
                        <label style="font-size: 14px; font-weight: bold; color: #475569; display: block; margin-bottom: 8px;">
                            ¿Qué trámites atiende esta ventanilla?
                        </label>
                        <div style="max-height: 150px; overflow-y: auto; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; background: #f8fafc;">
                            <label v-for="tipo in tiposTurnosGlobales" :key="tipo.id" style="display: flex; align-items: center; gap: 10px; padding: 6px 0; cursor: pointer;">
                                <input type="checkbox" :value="tipo.id" v-model="form.tipo_turnos" style="width: 16px; height: 16px; accent-color: #10b981;">
                                <span style="font-size: 14px; color: #334155;">{{ tipo.descripcion }}</span>
                            </label>
                            <div v-if="tiposTurnosGlobales.length === 0" style="color: #94a3b8; font-size: 13px; text-align: center;">
                                No hay trámites registrados.
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                        <button type="button" @click="mostrarModal = false" style="padding: 10px 15px; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Cancelar</button>
                        <button type="submit" style="padding: 10px 15px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const Toast = Swal.mixin({
    toast: true,
    position: 'top',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

const cajas = ref([]);
const tiposTurnosGlobales = ref([]); 

// Paginación y Búsqueda
const buscador = ref('');
const paginaActual = ref(1);
const totalPaginas = ref(1);
const totalRegistros = ref(0);
let timeoutBuscador = null;

const mostrarModal = ref(false);
const modoEdicion = ref(false);

const form = ref({
    id: null,
    nombre: '',
    tipo_turnos: [] 
});

const cargarDatos = async (pagina = 1) => {
    try {
        const response = await axios.get(`/api/admin/cajas?page=${pagina}&search=${buscador.value}`);
        if (response.data.status === 'ok') {
            cajas.value = response.data.cajas.data;
            paginaActual.value = response.data.cajas.current_page;
            totalPaginas.value = response.data.cajas.last_page;
            totalRegistros.value = response.data.cajas.total;
            tiposTurnosGlobales.value = response.data.tipo_turnos; 
        }
    } catch (error) {
        console.error("Error cargando datos", error);
    }
};

const buscarConPausa = () => {
    if (timeoutBuscador) clearTimeout(timeoutBuscador);
    timeoutBuscador = setTimeout(() => {
        cargarDatos(1);
    }, 500); 
};

const abrirModalNuevo = () => {
    modoEdicion.value = false;
    form.value = { id: null, nombre: '', tipo_turnos: [] };
    mostrarModal.value = true;
};

const editarCaja = (caja) => {
    modoEdicion.value = true;
    const idsTiposAsignados = caja.tipos_de_turno ? caja.tipos_de_turno.map(t => t.id) : [];
    form.value = { 
        id: caja.id, 
        nombre: caja.nombre,
        tipo_turnos: idsTiposAsignados 
    };
    mostrarModal.value = true;
};

const guardarCaja = async () => {
    try {
        if (modoEdicion.value) {
            await axios.put(`/api/admin/cajas/${form.value.id}`, form.value);
        } else {
            await axios.post('/api/admin/cajas', form.value);
        }
        
        mostrarModal.value = false;
        cargarDatos(paginaActual.value); 
        
        Toast.fire({
            icon: 'success',
            title: modoEdicion.value ? 'Ventanilla actualizada' : 'Ventanilla creada con éxito'
        });

    } catch (error) {
        if (error.response && error.response.status === 422) {
            const errores = error.response.data.errors;
            let msjHTML = "<ul style='text-align: left; color: #ef4444;'>";
            for (let campo in errores) { msjHTML += `<li>${errores[campo][0]}</li>`; }
            msjHTML += "</ul>";
            Swal.fire({ icon: 'error', title: 'Verifica los datos', html: msjHTML, confirmButtonColor: '#3b82f6' });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error inesperado.', confirmButtonColor: '#3b82f6' });
        }
    }
};

const cambiarEstado = async (caja) => {
    const accion = caja.is_active ? 'dar de baja' : 'reactivar';
    const textoAlerta = caja.is_active 
        ? `La ventanilla "${caja.nombre}" será desactivada operativamente.` 
        : `La ventanilla "${caja.nombre}" será reactivada y podrá operar de nuevo.`;
    const colorBoton = caja.is_active ? '#ef4444' : '#10b981'; 
    const estadoFinal = caja.is_active ? '¡Dada de Baja!' : '¡Reactivada!';

    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: textoAlerta,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: colorBoton,
        cancelButtonColor: '#94a3b8',
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            await axios.patch(`/api/admin/cajas/${caja.id}/toggle`);
            cargarDatos(paginaActual.value); 
            Toast.fire({ icon: 'success', title: estadoFinal });
        } catch (error) {
            Swal.fire('Error', 'No se pudo cambiar el estado.', 'error');
        }
    }
};

onMounted(() => {
    cargarDatos(1);
});
</script>