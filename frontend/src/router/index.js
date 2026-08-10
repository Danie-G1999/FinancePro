import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '../modules/Auth/views/LoginView.vue'
import DashboardView from '../modules/Dashboard/views/DashboardView.vue'
import { useAuthStore } from '../modules/Auth/stores/authStore'

const routes = [
    {
        path: '/',
        redirect: '/dashboard',
    },

    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: {
            guest: true,
        },
    },

    {
        path: '/dashboard',
        name: 'dashboard',
        component: DashboardView,
        meta: {
            requiresAuth: true,
        },
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to) => {
    const authStore = useAuthStore()

    if (!authStore.initialized) {
        await authStore.fetchUser()
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return {
            name: 'login',
        }
    }

    if (to.meta.guest && authStore.isAuthenticated) {
        return {
            name: 'dashboard',
        }
    }

    return true
})

export default router