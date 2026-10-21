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

        // true si no se pudo hablar con el backend (red, CORS, servidor caído).
        const connectionError = ref(false)

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
            } catch (error) {
                // No bloquea al usuario, pero el recorrido reaparecerá al recargar: debe verse en consola.
                console.error('No fue posible guardar el recorrido guiado como visto:', error)
            }
        }

        // Registra la aceptación de los documentos legales pendientes (Ley 1581 de 2012).
        async function acceptLegal(documents) {
            const response = await api.post('/legal/accept', { documents })

            user.value = response.data.data
        }

        // Nunca lanza: ante cualquier fallo el usuario queda como invitado y el
        // guardián de rutas lo manda al login (antes un error de red dejaba la app en blanco).
        async function fetchUser() {
            try {
                const response = await api.get('/auth/me')

                user.value = response.data.data
                connectionError.value = false

                return user.value
            } catch (error) {
                user.value = null

                // Sin respuesta (red/CORS) o error del servidor: no es "sin sesión", es que no hay conexión.
                connectionError.value = !error.response || error.response.status >= 500

                return null
            } finally {
                initialized.value = true
            }
        }

        // Sesión vencida o cerrada en el servidor mientras la app estaba abierta.
        function clearSession() {
            user.value = null
        }

        // Cierra la sesión y recarga en /login: borra de memoria todos los datos del
        // usuario (stores de Pinia) y cualquier dato de la app en el navegador.
        // reason: 'inactive' muestra en el login "Tu sesión ha expirado por inactividad."
        async function logoutAndRedirect(reason = null) {
            await logout()

            try {
                sessionStorage.clear()
            } catch {
                // Navegación privada o almacenamiento bloqueado: no hay nada que limpiar.
            }

            window.location.replace(reason ? `/login?reason=${reason}` : '/login')
        }

        // Guarda la configuración del usuario; la app la lee de user.settings.
        async function updateSettings(data) {
            const response = await api.put('/user/settings', data)

            if (user.value) {
                user.value = { ...user.value, settings: response.data.data }
            }

            return response.data
        }

        return {
            user,
            loading,
            initialized,
            connectionError,
            isAuthenticated,
            login,
            register,
            logout,
            logoutAndRedirect,
            clearSession,
            fetchUser,
            completeOnboarding,
            acceptLegal,
            updateSettings,
        }
    }
)