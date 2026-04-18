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
        path: '/settings',
        name: 'Settings',
        component: () => import('./views/Settings.vue'),
        meta: { auth: true, adminOnly: true }
    },
    {
        path: '/counters',
        name: 'Counters',
        component: () => import('./views/Counters.vue'),
        meta: { auth: true, adminOnly: true }
    },
    {
        path: '/staff',
        name: 'Staff',
        component: () => import('./views/Staff.vue'),
        meta: { auth: true, adminOnly: true }
    },
    {
        path: '/history',
        name: 'History',
        component: () => import('./views/History.vue'),
        meta: { auth: true }
    },
    {
        path: '/analytics',
        name: 'Analytics',
        component: () => import('./views/Analytics.vue'),
        meta: { auth: true }
    },
    {
        path: '/subscription',
        name: 'Subscription',
        component: () => import('./views/Subscription.vue'),
        meta: { auth: true, adminOnly: true }
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
    } else if (to.meta.adminOnly && !auth.isClinicAdmin) {
        return '/';
    } else if (to.meta.guest && auth.isAuthenticated) {
        return '/';
    }
});

export default router;
