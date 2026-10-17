<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

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

function goToDashboard() {
    router.push({ name: 'dashboard' })
}
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

            <Transition
                name="auth-swap"
                mode="out-in"
            >
                <RegisterForm
                    v-if="isRegister"
                    key="register"
                    :initial-email="email"
                    @switch="switchForm"
                    @success="goToDashboard"
                />

                <LoginForm
                    v-else
                    key="login"
                    :initial-email="email"
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
