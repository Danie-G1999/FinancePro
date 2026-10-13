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

        async function logout() {
            try {
                await api.post('/auth/logout')
            } finally {
                user.value = null
                initialized.value = true
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
            logout,
            fetchUser,
        }
    }
)