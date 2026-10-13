import { createRouter, createWebHistory } from 'vue-router'

import MainLayout from '../layouts/MainLayout.vue'

import LoginView from '../modules/Auth/views/LoginView.vue'
import ProfileView from '../modules/Auth/views/ProfileView.vue'
import SettingsView from '../modules/Auth/views/SettingsView.vue'

import DashboardView from '../modules/Dashboard/views/DashboardView.vue'

import AccountsView from '../modules/Accounts/views/AccountsView.vue'
import TransactionsView from '../modules/Transactions/views/TransactionsView.vue'
import IncomeView from '../modules/Income/views/IncomeView.vue'
import ExpensesView from '../modules/Expenses/views/ExpensesView.vue'
import DebtsView from '../modules/Debts/views/DebtsView.vue'
import BudgetsView from '../modules/Budgets/views/BudgetsView.vue'
import ReportsView from '../modules/Reports/views/ReportsView.vue'

import { useAuthStore } from '../modules/Auth/stores/authStore'

const routes = [
    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: {
            guest: true,
        },
    },

    {
        path: '/',
        component: MainLayout,
        meta: {
            requiresAuth: true,
        },

        children: [
            {
                path: '',
                redirect: '/dashboard',
            },

            {
                path: 'dashboard',
                name: 'dashboard',
                component: DashboardView,
                meta: {
                    requiresAuth: true,
                },
            },

            {
                path: 'cuentas',
                name: 'cuentas',
                component: AccountsView,
                meta: {
                    requiresAuth: true,
                },
            },

            {
                path: 'movimientos',
                name: 'movimientos',
                component: TransactionsView,
                meta: {
                    requiresAuth: true,
                },
            },

            {
                path: 'ingresos',
                name: 'ingresos',
                component: IncomeView,
                meta: {
                    requiresAuth: true,
                },
            },

            {
                path: 'gastos',
                name: 'gastos',
                component: ExpensesView,
                meta: {
                    requiresAuth: true,
                },
            },

            {
                path: 'deudas',
                name: 'deudas',
                component: DebtsView,
                meta: {
                    requiresAuth: true,
                },
            },

            {
                path: 'presupuestos',
                name: 'presupuestos',
                component: BudgetsView,
                meta: {
                    requiresAuth: true,
                },
            },

            {
                path: 'reportes',
                name: 'reportes',
                component: ReportsView,
                meta: {
                    requiresAuth: true,
                },
            },

            {
                path: 'perfil',
                name: 'profile',
                component: ProfileView,
                meta: {
                    requiresAuth: true,
                },
            },

            {
                path: 'configuracion',
                name: 'settings',
                component: SettingsView,
                meta: {
                    requiresAuth: true,
                },
            },
        ],
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

    if (
        to.meta.requiresAuth &&
        !authStore.isAuthenticated
    ) {
        return {
            name: 'login',
        }
    }

    if (
        to.meta.guest &&
        authStore.isAuthenticated
    ) {
        return {
            name: 'dashboard',
        }
    }

    return true
})

export default router