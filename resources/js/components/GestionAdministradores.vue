<template>
    <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: #1e293b;">👥 Catálogo de Usuarios</h2>
            <button @click="abrirModalNuevo" style="background: #3b82f6; color: white; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                ➕ Nuevo Usuario
            </button>
        </div>

        <div v-if="mostrarModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;">
            <div style="background: white; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                
                <h3 style="margin-top: 0; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                    {{ modoEdicion ? '✏️ Editar Usuario' : '✨ Crear Nuevo Usuario' }}
                </h3>
                
                <div style="display: grid; grid-template-columns: 1fr; gap: 15px; margin-top: 20px; margin-bottom: 25px;">
                    
                    <div>
                        <label style="display: block; font-weight: bold; margin-bottom: 5px; color: #475569;">Nombre Completo:</label>
                        <input type="text" v-model="formulario.name" placeholder="Ej. Juan Pérez" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display: block; font-weight: bold; margin-bottom: 5px; color: #475569;">Correo (Login):</label>
                        <input type="email" v-model="formulario.email" placeholder="ejemplo@test.com" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="display: block; font-weight: bold; margin-bottom: 5px; color: #475569;">
                            Contraseña: <span v-if="modoEdicion" style="font-weight: normal; color: #94a3b8; font-size: 13px;">(Dejar en blanco para no cambiar)</span>
                        </label>
                        <input type="password" v-model="formulario.password" placeholder="Mínimo 6 caracteres" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <div style="flex: 1;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px; color: #475569;">Rol en el Sistema:</label>
                            <select v-model="formulario.rol_id" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                <option value="1">👑 Super Administrador</option>
                                <option value="2">🏢 Administrador de Sede</option>
                                <option value="3">👤 Cajero / Operador</option>
                            </select>
                        </div>

                        <div style="flex: 1;" v-if="formulario.rol_id != 1">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px; color: #475569;">Sede Asignada:</label>
                            <select v-model="formulario.sede_id" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                <option value="">-- Selecciona --</option>
                                <option v-for="sede in sedesDisponibles" :key="sede.id" :value="sede.id">
                                    {{ sede.nombre }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button @click="cerrarModal" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">Cancelar</button>
                    <button @click="guardarUsuario" style="background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">💾 Guardar</button>
                </div>

            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr style="background: #f8fafc; text-align: left;">
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0;">Usuario / Email</th>
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0;">Rol</th>
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0;">Sede</th>
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0;">Estado</th>
                    <th style="padding: 15px; border-bottom: 2px solid #e2e8f0; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in usuarios" :key="user.id" style="border-bottom: 1px solid #e2e8f0;" :style="{ opacity: user.is_active ? '1' : '0.6' }">
                    <td style="padding: 15px;">
                        <strong style="color: #334155;">{{ user.name }}</strong><br>
                        <span style="color: #64748b; font-size: 13px;">{{ user.email }}</span>
                    </td>
                    <td style="padding: 15px; font-weight: bold;">
                        <span v-if="user.rol_id === 1" style="color: #a855f7;">👑 Superadmin</span>
                        <span v-else-if="user.rol_id === 2" style="color: #3b82f6;">🏢 Admin Sede</span>
                        <span v-else-if="user.rol_id === 3" style="color: #f59e0b;">👤 Cajero</span>
                    </td>
                    <td style="padding: 15px; color: #64748b; font-size: 14px;">
                        {{ user.rol_id === 1 ? 'Global' : (user.sede ? user.sede.nombre : 'Sin Sede') }}
                    </td>
                    <td style="padding: 15px;">
                        <span v-if="user.is_active" style="color: #16a34a; font-weight: bold; background: #dcfce7; padding: 6px 12px; border-radius: 20px; font-size: 12px;">Activo</span>
                        <span v-else style="color: #dc2626; font-weight: bold; background: #fee2e2; padding: 6px 12px; border-radius: 20px; font-size: 12px;">Inactivo</span>
                    </td>
                    <td style="padding: 15px; text-align: right; white-space: nowrap; width: 1%;">
                        <button @click="editarUsuario(user)" style="background: #f59e0b; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; margin-right: 8px; font-weight: bold;">✏️ Editar</button>
                        
                        <template v-if="user.id !== 1">
                            <button v-if="user.is_active" @click="cambiarEstado(user.id)" style="background: #ef4444; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">❌ Baja</button>
                            <button v-else @click="cambiarEstado(user.id)" style="background: #10b981; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">✅ Reactivar</button>
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const usuarios = ref([]);
const sedesDisponibles = ref([]);

const mostrarModal = ref(false);
const modoEdicion = ref(false);

const formulario = ref({ id: null, name: '', email: '', password: '', rol_id: 2, sede_id: '' });

// 1. Cargar datos
const cargarUsuarios = async () => {
    try {
        const response = await axios.get('/api/superadmin/usuarios');
        usuarios.value = response.data.usuarios;
        sedesDisponibles.value = response.data.sedes_disponibles;
    } catch (error) {
        console.error("Error al cargar usuarios:", error);
    }
};

// 2. Modal
const abrirModalNuevo = () => {
    formulario.value = { id: null, name: '', email: '', password: '', rol_id: 2, sede_id: '' };
    modoEdicion.value = false;
    mostrarModal.value = true;
};

const editarUsuario = (user) => {
    formulario.value = { 
        id: user.id, 
        name: user.name, 
        email: user.email, 
        password: '', // En blanco por seguridad
        rol_id: user.rol_id, 
        sede_id: user.sede_id || '' 
    };
    modoEdicion.value = true;
    mostrarModal.value = true;
};

const cerrarModal = () => {
    mostrarModal.value = false;
};

// 3. Guardar
const guardarUsuario = async () => {
    if (!formulario.value.name || !formulario.value.email) {
        alert("Nombre y correo son obligatorios");
        return;
    }
    // Validamos que los mortales (Admin/Cajero) tengan sede
    if (formulario.value.rol_id != 1 && !formulario.value.sede_id) {
        alert("Debes asignar una sede para este rol.");
        return;
    }

    try {
        if (modoEdicion.value) {
            await axios.put(`/api/superadmin/usuarios/${formulario.value.id}`, formulario.value);
        } else {
            await axios.post('/api/superadmin/usuarios', formulario.value);
        }
        
        cerrarModal();
        cargarUsuarios();
        
    } catch (error) {
        if (error.response && error.response.status === 422) {
            const errores = error.response.data.errors;
            let msj = "";
            for (let campo in errores) { msj += errores[campo][0] + "\n"; }
            alert(msj);
        } else {
            console.error("Error al guardar:", error);
            alert("Error al guardar el usuario.");
        }
    }
};

// 4. Baja Lógica
const cambiarEstado = async (id) => {
    try {
        await axios.put(`/api/superadmin/usuarios/${id}/toggle`);
        cargarUsuarios(); 
    } catch (error) {
        alert(error.response?.data?.message || "Ocurrió un error al intentar cambiar el estado.");
    }
};

onMounted(() => {
    cargarUsuarios();
});
</script>