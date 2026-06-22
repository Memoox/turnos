<template>
    <div style="font-family: sans-serif; max-width: 400px; margin: 100px auto; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); border: 1px solid #e5e7eb;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="margin: 0; color: #1f2937; font-size: 28px;">🔑 Sistema de Turnos</h1>
            <p style="color: #6b7280; margin-top: 5px;">Ingresa tus credenciales para continuar</p>
        </div>

        <div v-if="errorMessage" style="background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #fca5a5;">
            {{ errorMessage }}
        </div>

        <form @submit.prevent="handleLogin">
            <div style="margin-bottom: 20px; text-align: left;">
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: bold; font-size: 14px;">Correo Electrónico</label>
                <input type="email" 
                       v-model="email" 
                       required
                       placeholder="cajero@test.com"
                       style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; box-sizing: border-box; font-size: 16px;">
            </div>

            <div style="margin-bottom: 30px; text-align: left;">
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: bold; font-size: 14px;">Contraseña</label>
                <input type="password" 
                       v-model="password" 
                       required
                       placeholder="••••••••"
                       style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; box-sizing: border-box; font-size: 16px;">
            </div>

            <button type="submit" 
                    :disabled="loading"
                    :style="{ opacity: loading ? '0.7' : '1', cursor: loading ? 'not-allowed' : 'pointer' }"
                    style="width: 100%; padding: 14px; background: #2563eb; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 6px rgba(37,99,235,0.2); display: flex; justify-content: center; align-items: center; gap: 10px; transition: all 0.3s ease;">
                
                <svg v-if="loading" class="spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                <span>{{ loading ? 'Iniciando sesión...' : 'Iniciar Sesión 🚀' }}</span>
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const email = ref('');
const password = ref('');
const errorMessage = ref('');
const loading = ref(false);

const handleLogin = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        await axios.get('/sanctum/csrf-cookie');

        const response = await axios.post('/api/login', {
            email: email.value,
            password: password.value
        });

        if (response.data.status === 'ok') {
            const user = response.data.user;
            const claveRol = user.rol.clave;
            
            localStorage.setItem('user_rol', claveRol);

            switch (claveRol) {
                case 'superadmin':
                    await router.push('/superadmin');
                    break;
                case 'admin':
                    await router.push('/admin');
                    break;
                case 'cajero':
                    await router.push('/cajero');
                    break;
                default:
                    await router.push('/login');
            }
        }
    } catch (error) {
        console.error("Error en el login:", error.response?.data || error);
        errorMessage.value = error.response?.data?.message || 'Error de conexión con el servidor.';
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
/* Animación para que el spinner gire */
.spinner {
    height: 20px;
    width: 20px;
    color: white;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>