import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: () => import('../views/auth/Login.vue') 
    },
    {
        path: '/pemohon/dashboard',
        name: 'DashboardPemohon',
        component: () => import('../views/pemohon/Dashboard.vue') 
    },
    {
        path: '/penilai/dashboard',
        name: 'DashboardPenilai',
        component: () => import('../views/penilai/Dashboard.vue')
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
