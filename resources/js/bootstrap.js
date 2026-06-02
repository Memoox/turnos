import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;

// --- EL NUEVO INTERCEPTOR GLOBAL ---
window.axios.interceptors.response.use(
    function (response) {
        return response;
    }, 
    function (error) {
        if (error.response && (error.response.status === 401 || error.response.status === 419)) {
            // Solo redirigimos si NO estamos ya en la pantalla de login
            if (window.location.pathname !== '/login') {
                console.log("Sesión expirada. Redirigiendo al login...");
                window.location.href = '/login'; 
            }
        }
        return Promise.reject(error);
    }
);
// Esto conecta la configuración de Reverb que Laravel instaló automáticamente
import './echo';