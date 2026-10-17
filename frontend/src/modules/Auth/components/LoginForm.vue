<script setup>
import { reactive, ref } from 'vue'

import { useAuthStore } from '../stores/authStore'
import { fieldErrors, useLockout } from '../composables/useLockout'

const props = defineProps({
    initialEmail: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['success', 'switch'])

const authStore = useAuthStore()

const { secondsLeft, locked, start } = useLockout()

const form = reactive({
    email: props.initialEmail,
    password: '',
    remember: false,
})

const showPassword = ref(false)
const errorMessage = ref('')
const errors = ref({})

async function submit() {
    errorMessage.value = ''
    errors.value = {}

    try {
        await authStore.login(form)

        emit('success')
    } catch (error) {
        const status = error.response?.status

        if (status === 429) {
            start(error.response.data?.retry_after ?? error.response.headers?.['retry-after'])
            errorMessage.value = error.response.data?.message
        } else if (status === 422) {
            errors.value = fieldErrors(error)

            // "Credenciales incorrectas" llega en el campo email; se muestra como aviso general.
            errorMessage.value = errors.value.email && !errors.value.password
                ? errors.value.email
                : ''

            if (errorMessage.value) {
                errors.value = {}
            }
        } else {
            errorMessage.value = 'No fue posible iniciar sesión. Revisa tu conexión e inténtalo de nuevo.'
        }

        form.password = ''
    }
}
</script>

<template>
    <form
        class="auth-form"
        novalidate
        @submit.prevent="submit"
    >
        <header class="auth-header">
            <h1>Iniciar sesión</h1>
            <p>Ingresa para ver tus cuentas y finanzas.</p>
        </header>

        <div class="field">
            <label for="login-email">Correo</label>

            <input
                id="login-email"
                v-model="form.email"
                class="form-input"
                :class="{ 'input-error': errors.email }"
                type="email"
                autocomplete="email"
                placeholder="tu@correo.com"
                required
            />

            <span v-if="errors.email" class="field-error">{{ errors.email }}</span>
        </div>

        <div class="field">
            <label for="login-password">Contraseña</label>

            <div class="password-field">
                <input
                    id="login-password"
                    v-model="form.password"
                    class="form-input"
                    :class="{ 'input-error': errors.password }"
                    :type="showPassword ? 'text' : 'password'"
                    autocomplete="current-password"
                    required
                />

                <button
                    type="button"
                    class="toggle-password"
                    :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    @click="showPassword = !showPassword"
                >
                    {{ showPassword ? 'Ocultar' : 'Mostrar' }}
                </button>
            </div>

            <span v-if="errors.password" class="field-error">{{ errors.password }}</span>
        </div>

        <label class="remember">
            <input
                v-model="form.remember"
                type="checkbox"
            />

            Recordarme
        </label>

        <div
            v-if="errorMessage"
            class="auth-alert"
            role="alert"
        >
            {{ errorMessage }}

            <strong v-if="locked">
                Podrás intentarlo en {{ secondsLeft }} s.
            </strong>
        </div>

        <button
            class="submit-btn"
            type="submit"
            :disabled="authStore.loading || locked"
        >
            <template v-if="locked">Bloqueado ({{ secondsLeft }} s)</template>
            <template v-else-if="authStore.loading">Ingresando...</template>
            <template v-else>Iniciar sesión</template>
        </button>

        <p class="auth-switch">
            ¿No tienes cuenta?

            <button
                type="button"
                class="link-btn"
                @click="emit('switch', form.email)"
            >
                Regístrate
            </button>
        </p>
    </form>
</template>

<style scoped src="../styles/auth-form.css"></style>
