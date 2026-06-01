import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Esto conecta la configuración de Reverb que Laravel instaló automáticamente
import './echo';