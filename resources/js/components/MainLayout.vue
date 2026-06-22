<template>
    <div style="font-family: sans-serif; background-color: #f8fafc; min-height: 100vh;">
        
        <header style="background: linear-gradient(90deg, #1e293b 0%, #0f172a 100%); padding: 15px 40px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); display: flex; justify-content: space-between; align-items: center;">
            
            <div style="display: flex; align-items: center; gap: 15px;">
                <span style="font-size: 26px;">🏛️</span>
                <h2 style="margin: 0; color: #ffffff; letter-spacing: 0.5px; font-weight: 600;">Sistema de Turnos</h2>
            </div>
            
            <div style="display: flex; align-items: center; gap: 20px;">
                <span style="color: #e2e8f0; font-size: 14px; font-weight: bold; background: rgba(255, 255, 255, 0.1); padding: 6px 14px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.2);">
                 👤 {{ rolUsuario }}
                </span>
                
                <button @click="cerrarSesion" style="padding: 8px 16px; background: rgba(239, 68, 68, 0.9); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 14px; transition: background 0.3s ease;" onmouseover="this.style.background='#ef4444'" onmouseout="this.style.background='rgba(239, 68, 68, 0.9)'">
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
import Swal from 'sweetalert2';

const router = useRouter();
const rolUsuario = ref('');

onMounted(() => {
    const rol = localStorage.getItem('user_rol') || 'Desconocido';
    rolUsuario.value = rol.charAt(0).toUpperCase() + rol.slice(1);
});

const cerrarSesion = async () => {

    Swal.fire({
        title: 'Cerrando sesión...',
        text: 'Por favor espera un momento.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading(); // Pone el circulito girando
        }
    });

    try {
        await axios.post('/api/logout');
    } catch (error) {
        console.error("Error al cerrar sesión", error);
    } finally {
        localStorage.removeItem('user_rol');
        Swal.close();
        router.push('/login');
    }
};
</script>