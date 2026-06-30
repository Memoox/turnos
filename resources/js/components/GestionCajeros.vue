<template>
    <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h2 style="margin: 0; color: #1e293b;">👥 Personal de Ventanillas</h2>
            
            <div style="display: flex; gap: 10px; flex: 1; max-width: 500px;">
                <input 
                    v-model="buscador" 
                    @input="buscarConPausa" 
                    type="text" 
                    placeholder="🔍 Buscar por nombre o correo..." 
                    style="flex: 1; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; outline: none;"
                >
            </div>
            <button @click="abrirModalNuevo" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
                + Nuevo Cajero
            </button>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; color: #64748b; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px;">Nombre</th>
                    <th style="padding: 12px;">Correo</th>
                    <th style="padding: 12px;">Ventanilla Asignada</th>
                    <th style="padding: 12px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="cajero in cajeros" :key="cajero.id" 
                    :style="{ 
                        borderBottom: '1px solid #f1f5f9', 
                        opacity: cajero.is_active ? '1' : '0.6', 
                        backgroundColor: cajero.is_active ? 'transparent' : '#f8fafc' 
                    }">
                    
                    <td style="padding: 12px; font-weight: bold; color: #334155;">
                        {{ cajero.name }}
                        <span v-if="!cajero.is_active" style="margin-left: 8px; font-size: 11px; background: #cbd5e1; color: white; padding: 2px 6px; border-radius: 4px;">Inactivo</span>
                    </td>
                    <td style="padding: 12px; color: #64748b;">{{ cajero.email }}</td>
                    <td style="padding: 12px;">
                        <span v-if="cajero.caja && cajero.is_active" style="background: #dcfce7; color: #16a34a; padding: 4px 8px; border-radius: 6px; font-size: 14px; font-weight: bold;">
                            {{ cajero.caja.nombre }}
                        </span>
                        <span v-else style="background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 6px; font-size: 14px;">
                            Sin asignar
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: right; white-space: nowrap; width: 1%;">
                        <button v-if="cajero.is_active" @click="editarCajero(cajero)" style="background: #f59e0b; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; margin-right: 8px; font-weight: bold;">✏️ Editar</button>
                        
                        <button v-if="cajero.is_active" @click="cambiarEstado(cajero)" style="background: #ef4444; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">❌ Baja</button>
                        <button v-else @click="cambiarEstado(cajero)" style="background: #10b981; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">✅ Reactivar</button>
                    </td>
                </tr>
                <tr v-if="cajeros.length === 0">
                    <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8; font-size: 16px;">
                        No se encontraron cajeros con ese criterio.
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="totalRegistros > 0" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-top: 1px solid #e2e8f0; background: #f8fafc; border-radius: 0 0 12px 12px; margin-top: 10px;">
    
            <span style="color: #64748b; font-size: 14px;">
                Total: <strong>{{ totalRegistros }}</strong> registros
            </span>
            
            <div v-if="totalPaginas > 1" style="display: flex; gap: 8px; align-items: center;">
                <button 
                    :disabled="paginaActual === 1" 
                    @click="cargarDatos(paginaActual - 1)" 
                    style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: bold; cursor: pointer; transition: 0.2s;"
                    :style="{ background: paginaActual === 1 ? '#f1f5f9' : 'white', color: paginaActual === 1 ? '#94a3b8' : '#334155' }">
                    ⬅️ Anterior
                </button>

                <span style="padding: 0 10px; font-weight: bold; color: #3b82f6;">
                    Pág {{ paginaActual }} de {{ totalPaginas }}
                </span>

                <button 
                    :disabled="paginaActual === totalPaginas" 
                    @click="cargarDatos(paginaActual + 1)" 
                    style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: bold; cursor: pointer; transition: 0.2s;"
                    :style="{ background: paginaActual === totalPaginas ? '#f1f5f9' : 'white', color: paginaActual === totalPaginas ? '#94a3b8' : '#334155' }">
                    Siguiente ➡️
                </button>
            </div>
        </div>

        <div v-if="mostrarModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center;">
            <div style="background: white; padding: 30px; border-radius: 12px; width: 400px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #0f172a;">{{ modoEdicion ? '✏️ Editar Cajero' : '✨ Registrar Nuevo Cajero' }}</h3>
                
                <form @submit.prevent="guardarCajero" style="display: flex; flex-direction: column; gap: 15px;">
                    <div>
                        <label style="font-size: 14px; font-weight: bold; color: #475569;">Nombre Completo:</label>
                        <input v-model="form.name" type="text" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
                    </div>
                    
                    <div>
                        <label style="font-size: 14px; font-weight: bold; color: #475569;">Correo Electrónico:</label>
                        <input v-model="form.email" type="email" autocomplete="off" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
                    </div>
                    
                    <div>
                        <label style="font-size: 14px; font-weight: bold; color: #475569;">Contraseña <span v-if="modoEdicion" style="font-weight: normal; font-size: 12px; color: #94a3b8;">(Dejar en blanco para no cambiar)</span>:</label>
                        <input v-model="form.password" type="password" autocomplete="new-password" :required="!modoEdicion" style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
                    </div>

                    <div>
                        <label style="font-size: 14px; font-weight: bold; color: #475569;">Asignar Ventanilla:</label>
                        <select v-model="form.caja_id" style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
                            <option value="">-- Sin ventanilla (Descanso) --</option>
                            <option v-for="caja in cajasDisponibles" :key="caja.id" :value="caja.id">
                                {{ caja.nombre }}
                            </option>
                        </select>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                        <button type="button" @click="mostrarModal = false" style="padding: 10px 15px; background: #e2e8f0; color: #475569; font-weight: bold; border: none; border-radius: 6px; cursor: pointer;">Cancelar</button>
                        <button type="submit" style="padding: 10px 15px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const Toast = Swal.mixin({
    toast: true,
    position: 'top',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

const cajeros = ref([]);
const cajas = ref([]);

const buscador = ref('');
const paginaActual = ref(1);
const ultimaPagina = ref(1);
const totalPaginas = ref(1);
const totalRegistros = ref(0);

const mostrarModal = ref(false);
const modoEdicion = ref(false);

let timeoutBuscador = null;

const form = ref({
    id: null,
    name: '',
    email: '',
    password: '',
    caja_id: ''
});

const cargarDatos = async (pagina = 1) => {
    try {
        const response = await axios.get(`/api/admin/cajeros?page=${pagina}&search=${buscador.value}`);
        if (response.data.status === 'ok') {
            cajeros.value = response.data.cajeros.data;
            paginaActual.value = response.data.cajeros.current_page;
            totalPaginas.value = response.data.cajeros.last_page;
            totalRegistros.value = response.data.cajeros.total;
            cajas.value = response.data.cajas;
        }
    } catch (error) {
        console.error("Error cargando personal", error);
    }
};

const abrirModalNuevo = () => {
    modoEdicion.value = false;
    form.value = { id: null, name: '', email: '', password: '', caja_id: '' };
    mostrarModal.value = true;
};

const editarCajero = (cajero) => {
    modoEdicion.value = true;
    form.value = {
        id: cajero.id,
        name: cajero.name,
        email: cajero.email,
        password: '', 
        caja_id: cajero.caja_id || ''
    };
    mostrarModal.value = true;
};

const guardarCajero = async () => {
    if (!form.value.name || !form.value.email) {
        Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'El nombre y el correo son obligatorios.' });
        return;
    }

    try {
        if (modoEdicion.value) {
            await axios.put(`/api/admin/cajeros/${form.value.id}`, form.value);
        } else {
            await axios.post('/api/admin/cajeros', form.value);
        }
        mostrarModal.value = false;
        cargarDatos(paginaActual.value); 

        Toast.fire({
            icon: 'success',
            title: modoEdicion.value ? 'Cajero actualizado' : 'Cajero registrado con éxito'
        });
       
    } catch (error) {
        if (error.response && error.response.status === 422) {
            const errores = error.response.data.errors;
            let msjHTML = "<ul style='text-align: left; color: #ef4444;'>";
            for (let campo in errores) { msjHTML += `<li>${errores[campo][0]}</li>`; }
            msjHTML += "</ul>";
            Swal.fire({ icon: 'error', title: 'Error de validación', html: msjHTML, confirmButtonColor: '#3b82f6' });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar el cajero.', confirmButtonColor: '#3b82f6' });
        }
    }
};

const cambiarEstado = async (cajero) => {
    const accion = cajero.is_active ? 'dar de baja' : 'reactivar';
    const textoAlerta = cajero.is_active 
        ? 'El cajero será desactivado y perderá su acceso al sistema.' 
        : 'El cajero será reactivado y podrá volver a acceder al sistema.';
    const colorBoton = cajero.is_active ? '#ef4444' : '#10b981'; 
    const estadoFinal = cajero.is_active ? '¡Dado de Baja!' : '¡Reactivado!';

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
            await axios.patch(`/api/admin/cajeros/${cajero.id}/toggle`);
            cargarDatos(paginaActual.value); 
            
            Swal.fire(
                estadoFinal,
                `El cajero ha sido ${cajero.is_active ? 'desactivado' : 'reactivado'} exitosamente.`,
                'success'
            );
        } catch (error) {
            Swal.fire('Error', `No se pudo ${accion} al cajero.`, 'error');
        }
    }
};

const cajasDisponibles = computed(() => {
    return cajas.value.filter(caja => {
        const estaOcupada = cajeros.value.some(c => c.caja_id === caja.id);
        if (modoEdicion.value && form.value.caja_id === caja.id) {
            return true;
        }
        return !estaOcupada;
    });
});

const buscarConPausa = () => {
    if (timeoutBuscador) {
        clearTimeout(timeoutBuscador);
    }
 
    timeoutBuscador = setTimeout(() => {
        cargarDatos(1);
    }, 600); 
};

onMounted(() => {
    cargarDatos(1);
});
</script>