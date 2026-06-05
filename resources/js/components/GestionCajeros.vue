<template>
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: #1e293b;">👥 Personal de Ventanillas</h2>
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
                    <th style="padding: 12px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="cajero in cajeros" :key="cajero.id" style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; font-weight: bold; color: #334155;">{{ cajero.name }}</td>
                    <td style="padding: 12px; color: #64748b;">{{ cajero.email }}</td>
                    <td style="padding: 12px;">
                        <span v-if="cajero.caja" style="background: #dcfce7; color: #16a34a; padding: 4px 8px; border-radius: 6px; font-size: 14px; font-weight: bold;">
                            {{ cajero.caja.nombre }}
                        </span>
                        <span v-else style="background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 6px; font-size: 14px;">
                            Sin asignar
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarCajero(cajero)" style="margin-right: 10px; padding: 6px 12px; background: #eab308; color: white; border: none; border-radius: 4px; cursor: pointer;">✏️ Editar</button>
                        <button @click="eliminarCajero(cajero.id)" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">🗑️ Baja</button>
                    </td>
                </tr>
                <tr v-if="cajeros.length === 0">
                    <td colspan="4" style="text-align: center; padding: 20px; color: #94a3b8;">No hay cajeros registrados en esta sede.</td>
                </tr>
            </tbody>
        </table>

        <div v-if="mostrarModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center;">
            <div style="background: white; padding: 30px; border-radius: 12px; width: 400px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;">{{ modoEdicion ? 'Editar Cajero' : 'Registrar Nuevo Cajero' }}</h3>
                
                <form @submit.prevent="guardarCajero" style="display: flex; flex-direction: column; gap: 15px;">
                    <div>
                        <label style="font-size: 14px; font-weight: bold; color: #475569;">Nombre Completo:</label>
                        <input v-model="form.name" type="text" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    
                    <div>
                        <label style="font-size: 14px; font-weight: bold; color: #475569;">Correo Electrónico:</label>
                        <input v-model="form.email" type="email" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    
                    <div>
                        <label style="font-size: 14px; font-weight: bold; color: #475569;">Contraseña <span v-if="modoEdicion" style="font-weight: normal; font-size: 12px;">(Dejar en blanco para no cambiar)</span>:</label>
                        <input v-model="form.password" type="password" :required="!modoEdicion" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>

                    <div>
                        <label style="font-size: 14px; font-weight: bold; color: #475569;">Asignar Ventanilla:</label>
                        <select v-model="form.caja_id" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 6px;">
                            <option value="">-- Sin ventanilla (Descanso) --</option>
                            <option v-for="caja in cajasDisponibles" :key="caja.id" :value="caja.id">
                                {{ caja.nombre }}
                            </option>
                        </select>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                        <button type="button" @click="mostrarModal = false" style="padding: 8px 15px; background: #e2e8f0; border: none; border-radius: 6px; cursor: pointer;">Cancelar</button>
                        <button type="submit" style="padding: 8px 15px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Guardar</button>
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

const cajeros = ref([]);
const cajas = ref([]);

const mostrarModal = ref(false);
const modoEdicion = ref(false);

const form = ref({
    id: null,
    name: '',
    email: '',
    password: '',
    caja_id: ''
});

// Cargar datos iniciales
const cargarDatos = async () => {
    try {
        const response = await axios.get('/api/admin/cajeros');
        if (response.data.status === 'ok') {
            cajeros.value = response.data.cajeros;
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
        password: '', // Por seguridad no mostramos la encriptada
        caja_id: cajero.caja_id || ''
    };
    mostrarModal.value = true;
};

const guardarCajero = async () => {
    try {
        if (modoEdicion.value) {
            await axios.put(`/api/admin/cajeros/${form.value.id}`, form.value);
        } else {
            await axios.post('/api/admin/cajeros', form.value);
        }
        mostrarModal.value = false;
        cargarDatos(); // Recargar la tabla

        Swal.fire({
            icon: 'success',
            title: '¡Guardado!',
            text: 'El personal se guardó correctamente.',
            // timer: 3000,
            showConfirmButton: true   //false
        });
    } catch (error) {
        // DETECTAMOS ERRORES DE VALIDACIÓN (422)
        if (error.response && error.response.status === 422) {
            const erroresDeLaravel = error.response.data.errors;
            let mensajeAmigable = "";
            
            for (const campo in erroresDeLaravel) {
                mensajeAmigable += `• ${erroresDeLaravel[campo][0]}\n`;
            }
            
            // ALERTA DE ADVERTENCIA PARA EL FORMULARIO
            Swal.fire({
                icon: 'warning',
                title: 'Verifica tus datos',
                text: mensajeAmigable,
                confirmButtonColor: '#2563eb'
            });
        } else {
            // ALERTA DE ERROR CRÍTICO O DE RED
            Swal.fire({
                icon: 'error',
                title: 'Error de servidor',
                text: error.response?.data?.message || 'Ocurrió un problema inesperado.',
                confirmButtonColor: '#ef4444'
            });
        }
    }
};

const eliminarCajero = async (id) => {
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: "El cajero será dado de baja y perderá su acceso al sistema.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, dar de baja',
        cancelButtonText: 'Cancelar'
    });

    // SI EL USUARIO DICE QUE SÍ
    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/admin/cajeros/${id}`);
            cargarDatos();
            
            Swal.fire(
                '¡Eliminado!',
                'El cajero ha sido dado de baja exitosamente.',
                'success'
            );
        } catch (error) {
            Swal.fire('Error', 'No se pudo eliminar al cajero.', 'error');
        }
    }
};

const cajasDisponibles = computed(() => {
    return cajas.value.filter(caja => {
        // 1. Verificamos si alguien ya tiene esta caja
        const estaOcupada = cajeros.value.some(c => c.caja_id === caja.id);
        
        // 2. Si estamos editando a un cajero, SÍ debemos mostrarle su propia caja actual
        if (modoEdicion.value && form.value.caja_id === caja.id) {
            return true;
        }
        
        // 3. Solo devolvemos las que NO están ocupadas
        return !estaOcupada;
    });
});

onMounted(() => {
    cargarDatos();
});
</script>