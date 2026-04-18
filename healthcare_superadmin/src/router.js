import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './authStore';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: () => import('./views/Login.vue'),
        meta: { guest: true }
    },
    {
        path: '/',
        name: 'Dashboard',
        component: () => import('./views/Dashboard.vue'),
        meta: { auth: true }
    },
    {
        path: '/clinics',
        name: 'Clinics',
        component: () => import('./views/Clinics.vue'),
        meta: { auth: true }
    },
    {
        path: '/plans',
        name: 'Plans',
        component: () => import('./views/Plans.vue'),
        meta: { auth: true }
    },
    {
        path: '/payments',
        name: 'Payments',
        component: () => import('./views/Payments.vue'),
        meta: { auth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();
    
    if (auth.token && !auth.user) {
        await auth.fetchUser();
    }

    if (to.meta.auth && !auth.isAuthenticated) {
        return '/login';
    } else if (to.meta.guest && auth.isAuthenticated) {
        return '/';
    }
});

export default router;
