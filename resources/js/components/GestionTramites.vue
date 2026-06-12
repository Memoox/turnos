<template>
    <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h2 style="margin: 0; color: #1e293b;">📄 Catálogo de Trámites</h2>
            
            <div style="display: flex; gap: 10px; flex: 1; max-width: 500px;">
                <input type="text" v-model="buscador" @keyup.enter="cargarTramites(1)" placeholder="🔍 Buscar por clave o descripción..." style="flex: 1; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; outline: none;">
                <button @click="cargarTramites(1)" style="background: #334155; color: white; border: none; padding: 0 15px; border-radius: 6px; cursor: pointer; font-weight: bold;">Buscar</button>
                <button v-if="buscador" @click="limpiarBuscador" style="background: #ef4444; color: white; border: none; padding: 0 15px; border-radius: 6px; cursor: pointer; font-weight: bold;">✖</button>
            </div>

            <button @click="abrirModalNuevo" style="background: #3b82f6; color: white; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer; font-weight: bold;">➕ Nuevo Trámite</button>
        </div>

        <div v-if="mostrarModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;">
            <div style="background: white; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                
                <h3 style="margin-top: 0; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                    {{ modoEdicion ? '✏️ Editar Trámite' : '✨ Crear Nuevo Trámite' }}
                </h3>
                
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <div style="flex: 1;">
                        <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #475569;">Clave (Letra):</label>
                        <input type="text" v-model="formulario.clave" placeholder="Ej. D" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 16px; text-transform: uppercase;">
                    </div>
                    <div style="flex: 3;">
                        <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #475569;">Descripción del Trámite:</label>
                        <input type="text" v-model="formulario.descripcion" placeholder="Ej. Demanda Nueva" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 16px;">
                    </div>
                </div>

                <div style="margin-top: 20px; margin-bottom: 25px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 10px; color: #334155;">🏢 ¿En qué Sedes estará disponible?</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        
                        <label v-for="sede in sedesDisponibles" :key="sede.id" style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #475569;">
                            <input type="checkbox" :value="sede.id" v-model="formulario.sedes" style="width: 18px; height: 18px; cursor: pointer;">
                            {{ sede.nombre }}
                        </label>

                        <div v-if="sedesDisponibles.length === 0" style="color: #ef4444; font-size: 13px; grid-column: span 2;">
                            No hay sedes registradas en el sistema.
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button @click="cerrarModal" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">Cancelar</button>
                    <button @click="guardarTramite" style="background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">💾 Guardar</button>
                </div>

            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr style="background: #f8fafc; text-align: left;">
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0;">Clave</th>
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0;">Descripción</th>
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0;">Sedes Habilitadas</th>
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0;">Estado</th>
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="tramite in tramites" :key="tramite.id" style="border-bottom: 1px solid #e2e8f0;" :style="{ opacity: tramite.is_active ? '1' : '0.6' }">
                    <td style="padding: 15px; font-weight: black; color: #2563eb; font-size: 18px;">{{ tramite.clave }}</td>
                    <td style="padding: 15px; font-weight: bold; color: #334155;">
                        <span v-if="!tramite.is_active" style="text-decoration: line-through; color: #94a3b8;">{{ tramite.descripcion }}</span>
                        <span v-else>{{ tramite.descripcion }}</span>
                    </td>
                    <td style="padding: 15px; color: #64748b; font-size: 13px;">
                        <span v-if="tramite.sedes.length === 0" style="color: #ef4444;">Ninguna sede asignada</span>
                        <span v-else>{{ tramite.sedes.map(s => s.nombre).join(', ') }}</span>
                    </td>
                    <td style="padding: 15px;">
                        <span v-if="tramite.is_active" style="color: #16a34a; font-weight: bold; background: #dcfce7; padding: 6px 12px; border-radius: 20px; font-size: 12px;">Activo</span>
                        <span v-else style="color: #dc2626; font-weight: bold; background: #fee2e2; padding: 6px 12px; border-radius: 20px; font-size: 12px;">Inactivo</span>
                    </td>
                    <td style="padding: 15px; text-align: right; white-space: nowrap; width: 1%;">
                        <button @click="editarTramite(tramite)" style="background: #f59e0b; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; margin-right: 8px; font-weight: bold;">✏️ Editar</button>
                        <button v-if="tramite.is_active" @click="cambiarEstado(tramite.id)" style="background: #ef4444; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">❌ Baja</button>
                        <button v-else @click="cambiarEstado(tramite.id)" style="background: #10b981; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">✅ Reactivar</button>
                    </td>
                </tr>
                <tr v-if="tramites.length === 0">
                    <td colspan="5" style="text-align: center; padding: 30px; color: #64748b; font-size: 16px;">No hay trámites registrados.</td>
                </tr>
            </tbody>
        </table>

        <div v-if="totalRegistros > 0" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-top: 1px solid #e2e8f0; background: #f8fafc; border-radius: 0 0 12px 12px;">
            <span style="color: #64748b; font-size: 14px;">Total: <strong>{{ totalRegistros }}</strong> trámites registrados</span>
            
            <div style="display: flex; gap: 8px; align-items: center;">
                <button 
                    :disabled="paginaActual === 1" 
                    @click="cargarTramites(paginaActual - 1)" 
                    style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: bold; cursor: pointer; transition: 0.2s;"
                    :style="{ background: paginaActual === 1 ? '#f1f5f9' : 'white', color: paginaActual === 1 ? '#94a3b8' : '#334155' }">
                    ⬅️ Anterior
                </button>

                <span style="padding: 0 10px; font-weight: bold; color: #3b82f6;">
                    Pág {{ paginaActual }} de {{ ultimaPagina }}
                </span>

                <button 
                    :disabled="paginaActual === ultimaPagina" 
                    @click="cargarTramites(paginaActual + 1)" 
                    style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: bold; cursor: pointer; transition: 0.2s;"
                    :style="{ background: paginaActual === ultimaPagina ? '#f1f5f9' : 'white', color: paginaActual === ultimaPagina ? '#94a3b8' : '#334155' }">
                    Siguiente ➡️
                </button>
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

const tramites = ref([]);
const sedesDisponibles = ref([]);

const mostrarModal = ref(false);
const modoEdicion = ref(false);

const buscador = ref('');
const paginaActual = ref(1);
const ultimaPagina = ref(1);
const totalRegistros = ref(0);

// El formulario ahora incluye un arreglo de 'sedes'
const formulario = ref({ id: null, clave: '', descripcion: '', sedes: [] });

// 1. Cargar datos
const cargarTramites = async (page = 1) => {
    try {
        const response = await axios.get(`/api/superadmin/tramites?page=${page}&search=${buscador.value}`);
        tramites.value = response.data.tramites.data;
        paginaActual.value = response.data.tramites.current_page;
        ultimaPagina.value = response.data.tramites.last_page;
        totalRegistros.value = response.data.tramites.total;
        sedesDisponibles.value = response.data.sedes_disponibles;
    } catch (error) {
        console.error("Error al cargar trámites:", error);
    }
};

// 2. Controladores del Modal
const abrirModalNuevo = () => {
    formulario.value = { id: null, clave: '', descripcion: '', sedes: [] };
    modoEdicion.value = false;
    mostrarModal.value = true;
};

const editarTramite = (tramite) => {
    // Al editar, mapeamos las sedes que ya tiene asignadas para que los checkboxes se palomeen solos
    const sedesAsignadas = tramite.sedes.map(sede => sede.id);
    
    formulario.value = { 
        id: tramite.id, 
        clave: tramite.clave, 
        descripcion: tramite.descripcion, 
        sedes: sedesAsignadas 
    };
    modoEdicion.value = true;
    mostrarModal.value = true;
};

const cerrarModal = () => {
    mostrarModal.value = false;
};

// 3. Crear o Actualizar
const guardarTramite = async () => {
    if (!formulario.value.clave.trim() || !formulario.value.descripcion.trim()) {
        Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'La clave y la descripción son obligatorias.' });
        return;
    }

    try {
        if (modoEdicion.value) {
            await axios.put(`/api/superadmin/tramites/${formulario.value.id}`, formulario.value);
        } else {
            await axios.post('/api/superadmin/tramites', formulario.value);
        }
        
        cerrarModal();
        cargarTramites(1);

        Toast.fire({
            icon: 'success',
            title: modoEdicion.value ? 'Trámite actualizado' : 'Trámite creado con éxito'
        });
        
    } catch (error) {
        if (error.response && error.response.status === 422) {
            const errores = error.response.data.errors;
            let msjHTML = "<ul style='text-align: left; color: #ef4444;'>";
            for (let campo in errores) { msjHTML += `<li>${errores[campo][0]}</li>`; }
            msjHTML += "</ul>";

            Swal.fire({ icon: 'error', title: 'Verifica los datos', html: msjHTML, confirmButtonColor: '#3b82f6' });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error al guardar.', confirmButtonColor: '#3b82f6' });
        }
    }
};

// 4. SoftDelete
const cambiarEstado = async (id) => {
    const result = await Swal.fire({
        title: '¿Cambiar estado operativo?',
        text: "Los cajeros ya no podrán ver este trámite si lo das de baja.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            await axios.put(`/api/superadmin/tramites/${id}/toggle`);
            cargarTramites(1); 
            Toast.fire({ icon: 'success', title: 'Estado actualizado' });
        } catch (error) {
            Swal.fire('Error', 'No se pudo cambiar el estado.', 'error');
        }
    }
};

const limpiarBuscador = () => {
    buscador.value = '';
    cargarTramites(1);
};

onMounted(() => {
    cargarTramites(1);
});
</script>