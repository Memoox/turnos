<template>
    <div style="font-family: sans-serif; background-color: #f8fafc; min-height: 100vh;">
        
        <header style="background: white; padding: 15px 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <span style="font-size: 24px;">🏛️</span>
                <h2 style="margin: 0; color: #1e293b;">Sistema de Turnos</h2>
            </div>
            
            <div style="display: flex; align-items: center; gap: 20px;">
                <span style="color: #64748b; font-size: 14px; font-weight: bold; background: #f1f5f9; padding: 5px 10px; border-radius: 6px;">
                    Rol: {{ rolUsuario }}
                </span>
                <button @click="cerrarSesion" style="padding: 8px 16px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px;">
                    🚪 Cerrar Sesión
                </button>
            </div>
        </header>

        <main style="padding: 30px;">
            <router-view></router-view>
        </main>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const rolUsuario = ref('');

onMounted(() => {
    // Leemos el rol y le ponemos la primera letra en mayúscula para que se vea bonito
    const rol = localStorage.getItem('user_rol') || 'Desconocido';
    rolUsuario.value = rol.charAt(0).toUpperCase() + rol.slice(1);
});

const cerrarSesion = async () => {
    try {
        await axios.post('/api/logout');
    } catch (error) {
        console.error("Error al cerrar sesión", error);
    } finally {
        localStorage.removeItem('user_rol');
        router.push('/login');
    }
};
</script>