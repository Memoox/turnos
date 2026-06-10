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
                    <th style="padding: 12px; width: 10%;">ID</th>
                    <th style="padding: 12px; width: 30%;">Nombre</th>
                    <th style="padding: 12px; width: 40%;">Trámites Asignados</th>
                    <th style="padding: 12px; text-align: center; width: 20%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="caja in cajas" :key="caja.id" style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; color: #64748b;">#{{ caja.id }}</td>
                    <td style="padding: 12px; font-weight: bold; color: #334155;">{{ caja.nombre }}</td>
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
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarCaja(caja)" style="margin-right: 8px; padding: 6px 12px; background: #eab308; color: white; border: none; border-radius: 4px; cursor: pointer;">✏️</button>
                        <button @click="eliminarCaja(caja.id)" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">🗑️</button>
                    </td>
                </tr>
                <tr v-if="cajas.length === 0">
                    <td colspan="4" style="text-align: center; padding: 20px; color: #94a3b8;">No hay ventanillas registradas.</td>
                </tr>
            </tbody>
        </table>

        <div v-if="mostrarModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 50;">
            <div style="background: white; padding: 30px; border-radius: 12px; width: 450px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; margin-bottom: 20px; color: #1e293b;">{{ modoEdicion ? 'Editar Ventanilla' : 'Nueva Ventanilla' }}</h3>
                
                <form @submit.prevent="guardarCaja" style="display: flex; flex-direction: column; gap: 15px;">
                    <div>
                        <label style="font-size: 14px; font-weight: bold; color: #475569;">Nombre de la Ventanilla:</label>
                        <input v-model="form.nombre" type="text" placeholder="Ej. Ventanilla 1" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
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
                                No hay trámites registrados en el sistema global.
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                        <button type="button" @click="mostrarModal = false" style="padding: 10px 15px; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Cancelar</button>
                        <button type="submit" style="padding: 10px 15px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Guardar Ventanilla</button>
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
const tiposTurnosGlobales = ref([]); // Guardaremos el catálogo aquí

const mostrarModal = ref(false);
const modoEdicion = ref(false);

const form = ref({
    id: null,
    nombre: '',
    tipo_turnos: [] // Aquí Vue guardará los IDs de los checkboxes marcados
});

const cargarDatos = async () => {
    try {
        const response = await axios.get('/api/admin/cajas');
        if (response.data.status === 'ok') {
            cajas.value = response.data.cajas;
            tiposTurnosGlobales.value = response.data.tipo_turnos; // Cargamos el catálogo
        }
    } catch (error) {
        console.error("Error cargando datos", error);
    }
};

const abrirModalNuevo = () => {
    modoEdicion.value = false;
    form.value = { id: null, nombre: '', tipo_turnos: [] };
    mostrarModal.value = true;
};

const editarCaja = (caja) => {
    modoEdicion.value = true;
    
    // Mapeamos los tipos de turno actuales de esta caja para que los checkboxes se pre-seleccionen
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
        cargarDatos(); 
    } catch (error) {
        // Verificamos si es un error de validación de Laravel (Código 422)
        if (error.response && error.response.status === 422) {
            const erroresDeLaravel = error.response.data.errors;
            let mensajeAlerta = "";
            
            // Recorremos los errores y los juntamos (ej. "El nombre ya está en uso")
            for (let campo in erroresDeLaravel) {
                mensajeAlerta += erroresDeLaravel[campo][0] + "\n"; 
            }
            
            // Aquí puedes usar SweetAlert si lo prefieres: Swal.fire('Oops...', mensajeAlerta, 'error')
            alert(mensajeAlerta); 
        } else {
            console.error("Error grave:", error);
            alert("Ocurrió un error inesperado al guardar la ventanilla.");
        }
    }
};

const eliminarCaja = async (id) => {
    if (confirm("¿Estás seguro de eliminar esta ventanilla?")) {
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