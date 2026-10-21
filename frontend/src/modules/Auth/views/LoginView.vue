<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import api from '../../../api/axios'
import { safeRedirect } from '../../../utils/safeRedirect'
import { useAuthStore } from '../stores/authStore'

import LoginForm from '../components/LoginForm.vue'
import RegisterForm from '../components/RegisterForm.vue'

const route = useRoute()
const router = useRouter()

// La URL decide el formulario: /login o /registro (el botón "atrás" también funciona).
const isRegister = computed(() => route.name === 'register')

// El correo escrito pasa de un formulario al otro al cambiar.
const email = ref('')

function switchForm(currentEmail) {
    email.value = currentEmail ?? ''

    router.replace({ name: isRegister.value ? 'login' : 'register' })
}

const authStore = useAuthStore()

// Tras iniciar sesión vuelve a la página que pidió, si era una ruta interna válida.
function goToDashboard() {
    router.push(safeRedirect(route.query.redirect) ?? { name: 'dashboard' })
}

const sessionExpired = ref(route.query.expired === '1')

const closedForInactivity = ref(route.query.reason === 'inactive')

const googleEnabled = ref(false)

// Errores con los que el backend devuelve al usuario desde Google (?error=...).
const GOOGLE_ERRORS = {
    google_cancelled: 'Cancelaste el inicio de sesión con Google.',
    google_failed: 'No fue posible iniciar sesión con Google. Inténtalo de nuevo.',
    google_email_unverified: 'Tu correo de Google no está verificado. Verifícalo en Google o regístrate con correo y contraseña.',
    google_unavailable: 'El inicio de sesión con Google no está disponible en este momento.',
}

const googleError = ref(GOOGLE_ERRORS[route.query.error] ?? '')

onMounted(async () => {
    // Limpia los avisos de la URL para que no reaparezcan al recargar (conserva ?redirect).
    if (route.query.error || route.query.expired || route.query.reason) {
        const { error, expired, reason, ...rest } = route.query

        router.replace({ name: route.name, query: rest })
    }

    try {
        const response = await api.get('/auth/providers')

        googleEnabled.value = Boolean(response.data.data?.google)
    } catch {
        googleEnabled.value = false
    }
})
</script>

<template>
    <div class="login-page">
        <div class="card login-card">
            <div class="brand">
                <span class="brand-icon">F</span>

                <div>
                    <strong>FinancePro</strong>
                    <span>Finanzas personales</span>
                </div>
            </div>

            <div
                v-if="authStore.connectionError"
                class="google-error"
                role="alert"
            >
                No pudimos conectar con el servidor. Revisa tu conexión o inténtalo en unos minutos.
            </div>

            <div
                v-else-if="closedForInactivity"
                class="session-notice"
                role="status"
            >
                Tu sesión ha expirado por inactividad.
            </div>

            <div
                v-else-if="sessionExpired"
                class="session-notice"
                role="status"
            >
                Tu sesión expiró. Inicia sesión de nuevo para continuar.
            </div>

            <div
                v-if="googleError"
                class="google-error"
                role="alert"
            >
                {{ googleError }}
            </div>

            <Transition
                name="auth-swap"
                mode="out-in"
            >
                <RegisterForm
                    v-if="isRegister"
                    key="register"
                    :initial-email="email"
                    :google-enabled="googleEnabled"
                    @switch="switchForm"
                    @success="goToDashboard"
                />

                <LoginForm
                    v-else
                    key="login"
                    :initial-email="email"
                    :google-enabled="googleEnabled"
                    @switch="switchForm"
                    @success="goToDashboard"
                />
            </Transition>
        </div>
    </div>
</template>

<style scoped>
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.login-card {
    width: 100%;
    max-width: 420px;
    padding: 32px;
    overflow: hidden;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}

.brand-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    background: var(--color-primary);
    color: #fff;
    font-size: 19px;
    font-weight: 800;
}

.brand div {
    display: flex;
    flex-direction: column;
}

.brand strong {
    font-size: 16px;
    color: var(--color-text-primary);
}

.brand div span {
    font-size: 11px;
    color: var(--color-text-muted);
}

.google-error {
    margin-bottom: 16px;
    padding: 10px 12px;
    border: 1px solid var(--color-danger);
    border-radius: var(--radius-md);
    background: var(--color-danger-light);
    color: var(--color-danger);
    font-size: 13px;
}

.session-notice {
    margin-bottom: 16px;
    padding: 10px 12px;
    border: 1px solid var(--color-warning);
    border-radius: var(--radius-md);
    background: var(--color-warning-light);
    color: var(--color-text-primary);
    font-size: 13px;
}

.auth-swap-enter-active,
.auth-swap-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.auth-swap-enter-from {
    opacity: 0;
    transform: translateX(24px);
}

.auth-swap-leave-to {
    opacity: 0;
    transform: translateX(-24px);
}

@media (prefers-reduced-motion: reduce) {
    .auth-swap-enter-active,
    .auth-swap-leave-active {
        transition: none;
    }
}

@media (max-width: 480px) {
    .login-card {
        padding: 24px 20px;
    }
}
</style>
