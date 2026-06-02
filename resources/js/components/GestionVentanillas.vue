<template>
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: #1e293b;">🏢 Gestión de Ventanillas</h2>
            <button @click="abrirModalNuevo" style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
                + Nueva Ventanilla
            </button>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; color: #64748b; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">Nombre de la Ventanilla</th>
                    <th style="padding: 12px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="caja in cajas" :key="caja.id" style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; color: #64748b;">#{{ caja.id }}</td>
                    <td style="padding: 12px; font-weight: bold; color: #334155;">{{ caja.nombre }}</td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarCaja(caja)" style="margin-right: 10px; padding: 6px 12px; background: #eab308; color: white; border: none; border-radius: 4px; cursor: pointer;">✏️ Editar</button>
                        <button @click="eliminarCaja(caja.id)" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">🗑️ Eliminar</button>
                    </td>
                </tr>
                <tr v-if="cajas.length === 0">
                    <td colspan="3" style="text-align: center; padding: 20px; color: #94a3b8;">No hay ventanillas registradas en esta sede.</td>
                </tr>
            </tbody>
        </table>

        <div v-if="mostrarModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center;">
            <div style="background: white; padding: 30px; border-radius: 12px; width: 400px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;">{{ modoEdicion ? 'Editar Ventanilla' : 'Nueva Ventanilla' }}</h3>
                
                <form @submit.prevent="guardarCaja" style="display: flex; flex-direction: column; gap: 15px;">
                    <div>
                        <label style="font-size: 14px; font-weight: bold; color: #475569;">Nombre de la Ventanilla/Caja:</label>
                        <input v-model="form.nombre" type="text" placeholder="Ej. Caja 1, Atención Especial..." required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                        <button type="button" @click="mostrarModal = false" style="padding: 8px 15px; background: #e2e8f0; border: none; border-radius: 6px; cursor: pointer;">Cancelar</button>
                        <button type="submit" style="padding: 8px 15px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const cajas = ref([]);
const mostrarModal = ref(false);
const modoEdicion = ref(false);

const form = ref({
    id: null,
    nombre: ''
});

// Cargar ventanillas
const cargarDatos = async () => {
    try {
        const response = await axios.get('/api/admin/cajas');
        if (response.data.status === 'ok') {
            cajas.value = response.data.cajas;
        }
    } catch (error) {
        console.error("Error cargando ventanillas", error);
    }
};

const abrirModalNuevo = () => {
    modoEdicion.value = false;
    form.value = { id: null, nombre: '' };
    mostrarModal.value = true;
};

const editarCaja = (caja) => {
    modoEdicion.value = true;
    form.value = { id: caja.id, nombre: caja.nombre };
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
        cargarDatos(); 
    } catch (error) {
        alert("Error al guardar los datos.");
        console.error(error);
    }
};

const eliminarCaja = async (id) => {
    if (confirm("¿Estás seguro de que deseas eliminar esta ventanilla? Los cajeros asignados a ella quedarán 'Sin asignar'.")) {
        try {
            await axios.delete(`/api/admin/cajas/${id}`);
            cargarDatos();
        } catch (error) {
            console.error("Error al eliminar", error);
        }
    }
};

onMounted(() => {
    cargarDatos();
});
</script>