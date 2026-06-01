import './bootstrap'; // Esto carga Axios y Laravel Echo automáticamente
import { createApp } from 'vue';
import router from './router';


const app = createApp({});

app.use(router);


app.mount('#app');