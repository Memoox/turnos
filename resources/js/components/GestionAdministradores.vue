<template>
    <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
        
        <!-- ENCABEZADO Y BUSCADOR -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h2 style="margin: 0; color: #1e293b;">👥 Catálogo de Usuarios</h2>
            
            <div style="display: flex; gap: 10px; flex: 1; max-width: 500px;">
                <input 
                    type="text" 
                    v-model="buscador" 
                    @keyup.enter="cargarUsuarios(1)"
                    placeholder="🔍 Buscar por nombre o correo..." 
                    style="flex: 1; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; outline: none;"
                >
                <button @click="cargarUsuarios(1)" style="background: #334155; color: white; border: none; padding: 0 15px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                    Buscar
                </button>
                <button v-if="buscador" @click="limpiarBuscador" style="background: #ef4444; color: white; border: none; padding: 0 15px; border-radius: 6px; cursor: pointer; font-weight: bold; title: Limpiar búsqueda;">
                    ✖
                </button>
            </div>

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
                            <button v-if="user.is_active" @click="cambiarEstado(user.id)" style="background: #ef4444; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; margin-right: 8px; font-weight: bold;">❌ Baja</button>
                            <button v-else @click="cambiarEstado(user.id)" style="background: #10b981; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; margin-right: 8px; font-weight: bold;">✅ Reactivar</button>
                            <button v-if="user.id !== 1" @click="eliminarDefinitivo(user.id)" style="background: #7f1d1d; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;" title="Destruir Usuario">
                                🗑️
                            </button>
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="totalRegistros > 0" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-top: 1px solid #e2e8f0; background: #f8fafc; border-radius: 0 0 12px 12px;">
            <span style="color: #64748b; font-size: 14px;">Total: <strong>{{ totalRegistros }}</strong> usuarios registrados</span>
            
            <div style="display: flex; gap: 8px; align-items: center;">
                <button 
                    :disabled="paginaActual === 1" 
                    @click="cargarUsuarios(paginaActual - 1)" 
                    style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: bold; cursor: pointer; transition: 0.2s;"
                    :style="{ background: paginaActual === 1 ? '#f1f5f9' : 'white', color: paginaActual === 1 ? '#94a3b8' : '#334155' }">
                    ⬅️ Anterior
                </button>

                <span style="padding: 0 10px; font-weight: bold; color: #3b82f6;">
                    Pág {{ paginaActual }} de {{ ultimaPagina }}
                </span>

                <button 
                    :disabled="paginaActual === ultimaPagina" 
                    @click="cargarUsuarios(paginaActual + 1)" 
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

const usuarios = ref([]);
const sedesDisponibles = ref([]);

const mostrarModal = ref(false);
const modoEdicion = ref(false);

const formulario = ref({ id: null, name: '', email: '', password: '', rol_id: 2, sede_id: '' });

const paginaActual = ref(1);
const ultimaPagina = ref(1);
const totalRegistros = ref(0);

const buscador = ref('');

// 1. Cargar datos
const cargarUsuarios = async (page = 1) => {
    try {
        const response = await axios.get(`/api/superadmin/usuarios?page=${page}&search=${buscador.value}`);

        usuarios.value = response.data.usuarios.data;
        paginaActual.value = response.data.usuarios.current_page;
        ultimaPagina.value = response.data.usuarios.last_page;
        totalRegistros.value = response.data.usuarios.total;

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
        Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Nombre y correo son obligatorios.' });
        return;
    }
    // Validamos que los mortales (Admin/Cajero) tengan sede
    if (formulario.value.rol_id != 1 && !formulario.value.sede_id) {
        Swal.fire({ icon: 'warning', title: 'Falta Sede', text: 'Debes asignar una sede para este rol.' });
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

        Toast.fire({
            icon: 'success',
            title: modoEdicion.value ? 'Usuario actualizado' : 'Usuario registrado con éxito'
        });
        
    } catch (error) {
        if (error.response && error.response.status === 422) {
            const errores = error.response.data.errors;
            let msjHTML = "<ul style='text-align: left; color: #ef4444;'>";
            for (let campo in errores) { msjHTML += `<li>${errores[campo][0]}</li>`; }
            msjHTML += "</ul>";

            Swal.fire({ icon: 'error', title: 'Error en el formulario', html: msjHTML, confirmButtonColor: '#3b82f6' });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar el usuario.', confirmButtonColor: '#3b82f6' });
        }
    }
};

// 4. Baja Lógica
const cambiarEstado = async (id) => {
    const result = await Swal.fire({
        title: '¿Modificar acceso?',
        text: "Deseas cambiar el estado del usuario en el sistema.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Sí, cambiar estado',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            await axios.put(`/api/superadmin/usuarios/${id}/toggle`);
            cargarUsuarios(); 
            Toast.fire({ icon: 'success', title: 'Acceso actualizado' });
        } catch (error) {
            Swal.fire('Error', error.response?.data?.message || "Ocurrió un error al intentar cambiar el estado.", 'error');
        }
    }
};

// En el <script setup>
const eliminarDefinitivo = async (id) => {
    const result = await Swal.fire({
        title: '¿Destrucción Total?',
        text: "La cuenta del usuario será eliminada permanentemente del sistema.",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, destruir',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/superadmin/usuarios/${id}/force`);
            cargarUsuarios(paginaActual.value); 
            Toast.fire({ icon: 'success', title: 'Usuario destruido exitosamente' });
        } catch (error) {
            if (error.response && (error.response.status === 400 || error.response.status === 403)) {
                Swal.fire({ icon: 'warning', title: 'Acción Denegada', text: error.response.data.message, confirmButtonColor: '#3b82f6' });
            } else {
                Swal.fire('Error', 'Ocurrió un problema al intentar eliminar.', 'error');
            }
        }
    }
};

const limpiarBuscador = () => {
    buscador.value = '';
    cargarUsuarios(1);
};

onMounted(() => {
    cargarUsuarios(1);
});
</script>