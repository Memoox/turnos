import { createRouter, createWebHistory } from 'vue-router';
import PantallaTv from '../components/PantallaTv.vue';
import Kiosco from '../components/Kiosco.vue';
import PanelCajero from '../components/PanelCajero.vue';
import Login from '../components/Login.vue'; 
import PanelAdmin from '../components/PanelAdmin.vue';
import PanelSuperadmin from '../components/PanelSuperadmin.vue';
import MainLayout from '../components/MainLayout.vue';

const routes = [
    { path: '/login', name: 'login', component: Login, meta: { guestOnly: true } },
    { path: '/tv/:sede_id', name: 'pantalla-tv', component: PantallaTv },
    { path: '/kiosco/:sede_id', name: 'kiosco', component: Kiosco },
    
    {
        path: '/',
        component: MainLayout,
        meta: { requiresAuth: true }, // Protegemos todo el layout
        children: [
            { 
                path: 'cajero', 
                name: 'panel-cajero', 
                component: PanelCajero, 
                meta: { role: 'cajero' } 
            },
            { 
                path: 'admin', 
                name: 'panel-admin', 
                component: PanelAdmin, 
                meta: { role: 'admin' } 
            },
            { 
                path: 'superadmin', 
                name: 'panel-superadmin', 
                component: PanelSuperadmin, 
                meta: { role: 'superadmin' } 
            }
        ]
    },
    
    { path: '/:pathMatch(.*)*', redirect: '/login' }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const userRole = localStorage.getItem('user_rol');
    const isAuthenticated = !!userRole;

    // 1. Bloqueo de autenticación general
    if (to.meta.requiresAuth && !isAuthenticated) {
        return next('/login');
    }

    // 2. Bloqueo de ROLES específico (Si intenta entrar a una oficina que no le toca)
    if (to.meta.role && to.meta.role !== userRole) {
        alert('🚫 No tienes permiso para acceder a esta área.');
        // Lo regresamos a su oficina correspondiente
        if (userRole === 'cajero') return next('/cajero');
        if (userRole === 'admin') return next('/admin');
        if (userRole === 'superadmin') return next('/superadmin');
        return next('/login');
    }

    if (to.meta.guestOnly && isAuthenticated) {
        if (userRole === 'cajero') return next('/cajero');
        if (userRole === 'admin') return next('/admin');
        return next('/superadmin');
    }

    next();
});

export default router;