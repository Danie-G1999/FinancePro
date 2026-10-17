import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

import api from '../../../api/axios'
import sanctum from '../../../api/sanctum'

export const useAuthStore = defineStore(
    'auth',
    () => {
        const user = ref(null)
        const loading = ref(false)
        const initialized = ref(false)

        const isAuthenticated = computed(() => {
            return user.value !== null
        })

        async function login(credentials) {
            loading.value = true

            try {
                // Obtener cookie CSRF
                await sanctum.get('/sanctum/csrf-cookie')

                // Iniciar sesión
                const response = await api.post(
                    '/auth/login',
                    credentials
                )

                user.value = response.data.data

                return response
            } finally {
                loading.value = false
            }
        }

        // Crea la cuenta; el backend deja la sesión iniciada.
        async function register(data) {
            loading.value = true

            try {
                await sanctum.get('/sanctum/csrf-cookie')

                const response = await api.post('/auth/register', data)

                user.value = response.data.data

                return response
            } finally {
                loading.value = false
            }
        }

        async function logout() {
            try {
                await api.post('/auth/logout')
            } finally {
                user.value = null
                initialized.value = true
            }
        }

        // Guarda en el servidor que ya vio el recorrido, así no reaparece en otro navegador.
        async function completeOnboarding() {
            if (user.value) {
                user.value = { ...user.value, onboarding_completed: true }
            }

            try {
                const response = await api.post('/auth/onboarding/complete')

                user.value = response.data.data
            } catch {
                // Si falla, el recorrido solo volvería a mostrarse en la próxima carga.
            }
        }

        async function fetchUser() {
            try {
                const response = await api.get('/auth/me')

                user.value = response.data.data

                return user.value
            } catch (error) {
                if (error.response?.status === 401) {
                    user.value = null

                    return null
                }

                throw error
            } finally {
                initialized.value = true
            }
        }

        return {
            user,
            loading,
            initialized,
            isAuthenticated,
            login,
            register,
            logout,
            fetchUser,
            completeOnboarding,
        }
    }
)