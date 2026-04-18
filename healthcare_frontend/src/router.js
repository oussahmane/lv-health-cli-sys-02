import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        name: 'Home',
        component: () => import('./views/Home.vue'),
    },
    {
        path: '/q/:slug',
        name: 'Registration',
        component: () => import('./views/Home.vue'),
    },
    {
        path: '/q/:slug/find',
        name: 'FindTicket',
        component: () => import('./views/FindTicket.vue'),
    },
    {
        path: '/q/:slug/ticket/:id',
        name: 'Ticket',
        component: () => import('./views/Ticket.vue'),
    },
    {
        path: '/q/:slug/display',
        name: 'Display',
        component: () => import('./views/Display.vue'),
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
