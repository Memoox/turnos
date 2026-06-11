<template>
    <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: #1e293b;">🏢 Catálogo de Sedes</h2>
            <button @click="abrirModalNuevo" style="background: #3b82f6; color: white; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                ➕ Nueva Sede
            </button>
        </div>

        <div v-if="mostrarModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;">
            <div style="background: white; padding: 30px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
                
                <h3 style="margin-top: 0; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                    {{ modoEdicion ? '✏️ Editar Sede' : '✨ Crear Nueva Sede' }}
                </h3>
                
                <div style="margin-bottom: 20px; margin-top: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #475569;">Nombre de la Sede:</label>
                    <input type="text" v-model="formulario.nombre" placeholder="Ej. Sucursal Centro" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 16px;">
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button @click="cerrarModal" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                        Cancelar
                    </button>
                    <button @click="guardarSede" style="background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                        💾 Guardar
                    </button>
                </div>

            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr style="background: #f8fafc; text-align: left;">
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0;">ID</th>
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0;">Nombre</th>
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0;">Estado</th>
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="sede in sedes" :key="sede.id" style="border-bottom: 1px solid #e2e8f0;" :style="{ opacity: sede.status ? '1' : '0.6' }">
                    <td style="padding: 15px; color: #64748b;">{{ sede.id }}</td>
                    <td style="padding: 15px; font-weight: bold; color: #334155;">
                        <span v-if="!sede.status" style="text-decoration: line-through; color: #94a3b8;">{{ sede.nombre }}</span>
                        <span v-else>{{ sede.nombre }}</span>
                    </td>
                    <td style="padding: 15px;">
                        <span v-if="sede.status" style="color: #16a34a; font-weight: bold; background: #dcfce7; padding: 6px 12px; border-radius: 20px; font-size: 12px;">Activa</span>
                        <span v-else style="color: #dc2626; font-weight: bold; background: #fee2e2; padding: 6px 12px; border-radius: 20px; font-size: 12px;">Inactiva</span>
                    </td>
                    <td style="padding: 15px; text-align: right; white-space: nowrap; width: 1%;">
                        <button @click="editarSede(sede)" style="background: #f59e0b; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; margin-right: 8px; font-weight: bold;">
                            ✏️ Editar
                        </button>
                        
                        <button v-if="sede.status" @click="cambiarEstado(sede.id)" style="background: #ef4444; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                            ❌ Dar de baja
                        </button>
                        <button v-else @click="cambiarEstado(sede.id)" style="background: #10b981; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                            ✅ Reactivar
                        </button>
                    </td>
                </tr>
                <tr v-if="sedes.length === 0">
                    <td colspan="4" style="text-align: center; padding: 30px; color: #64748b; font-size: 16px;">No hay sedes registradas en el sistema.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const sedes = ref([]);
const mostrarModal = ref(false);
const modoEdicion = ref(false);
const formulario = ref({ id: null, nombre: '' });

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});


// 1. Cargar datos
const cargarSedes = async () => {
    try {
        const response = await axios.get('/api/superadmin/sedes');
        sedes.value = response.data.sedes;
    } catch (error) {
        console.error("Error al cargar sedes:", error);
    }
};

// 2. Controladores del Modal
const abrirModalNuevo = () => {
    formulario.value = { id: null, nombre: '' };
    modoEdicion.value = false;
    mostrarModal.value = true;
};

const editarSede = (sede) => {
    formulario.value = { id: sede.id, nombre: sede.nombre };
    modoEdicion.value = true;
    mostrarModal.value = true;
};

const cerrarModal = () => {
    mostrarModal.value = false;
};

// 3. Crear o Actualizar
const guardarSede = async () => {
    if (!formulario.value.nombre.trim()) {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'El nombre es obligatorio' });
        return;
    }

    try {
        if (modoEdicion.value) {
            await axios.put(`/api/superadmin/sedes/${formulario.value.id}`, formulario.value);
        } else {
            await axios.post('/api/superadmin/sedes', formulario.value);
        }
        
        cerrarModal();
        cargarSedes();

        Toast.fire({
            icon: 'success',
            title: modoEdicion.value ? 'Sede actualizada con éxito' : 'Sede creada con éxito'
        });
        
    } catch (error) {
        if (error.response && error.response.status === 422) {
            const errores = error.response.data.errors;
            let msjHTML = "<ul style='text-align: left; color: #ef4444;'>";
            
            // Convertimos los errores en una lista HTML
            for (let campo in errores) { 
                msjHTML += `<li>${errores[campo][0]}</li>`; 
            }
            msjHTML += "</ul>";

            // Lanzamos el modal de error de SweetAlert
            Swal.fire({
                icon: 'error',
                title: 'Verifica los datos',
                html: msjHTML,
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Entendido'
            });
        } else {
            console.error("Error al guardar:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error del sistema',
                text: 'Ocurrió un error inesperado. Contacta al administrador.',
                confirmButtonColor: '#3b82f6'
            });
        }
    }
};

// 4. Dar de baja / Reactivar (SoftDelete)
const cambiarEstado = async (id) => {
    // Le preguntamos al usuario antes de disparar al backend
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: "Cambiarás el estado operativo de este registro.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Sí, cambiar estado',
        cancelButtonText: 'Cancelar'
    });

    // Si el usuario dijo que sí, ejecutamos el Axios
    if (result.isConfirmed) {
        try {
            await axios.put(`/api/superadmin/sedes/${id}/toggle`);
            cargarSedes(); 
            
            Toast.fire({
                icon: 'success',
                title: 'Estado actualizado'
            });
        } catch (error) {
            Swal.fire('Error', 'No se pudo cambiar el estado.', 'error');
        }
    }
};

onMounted(() => {
    cargarSedes();
});
</script>