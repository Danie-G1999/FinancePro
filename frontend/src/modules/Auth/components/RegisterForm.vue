<script setup>
import { computed, reactive, ref } from 'vue'

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
    name: '',
    email: props.initialEmail,
    password: '',
    password_confirmation: '',
})

const showPassword = ref(false)
const errorMessage = ref('')
const errors = ref({})

// Mismas reglas que valida el backend (RegisterRequest).
const passwordRules = computed(() => [
    { label: 'Mínimo 8 caracteres', ok: form.password.length >= 8 },
    { label: 'Al menos una letra', ok: /\p{L}/u.test(form.password) },
    { label: 'Al menos un número', ok: /\d/.test(form.password) },
])

const passwordsMatch = computed(
    () => form.password_confirmation !== '' && form.password === form.password_confirmation
)

function validate() {
    errors.value = {}

    if (form.name.trim().length < 2) {
        errors.value.name = 'Escribe tu nombre (mínimo 2 caracteres).'
    }

    if (!/^\S+@\S+\.\S+$/.test(form.email.trim())) {
        errors.value.email = 'Ingresa un correo válido.'
    }

    if (!passwordRules.value.every((rule) => rule.ok)) {
        errors.value.password = 'La contraseña no cumple los requisitos.'
    }

    if (!passwordsMatch.value) {
        errors.value.password_confirmation = 'Las contraseñas no coinciden.'
    }

    return Object.keys(errors.value).length === 0
}

async function submit() {
    errorMessage.value = ''

    if (!validate()) {
        return
    }

    try {
        await authStore.register({
            name: form.name.trim(),
            email: form.email.trim(),
            password: form.password,
            password_confirmation: form.password_confirmation,
        })

        emit('success')
    } catch (error) {
        const status = error.response?.status

        if (status === 429) {
            start(error.response.data?.retry_after ?? error.response.headers?.['retry-after'])
            errorMessage.value = error.response.data?.message
        } else if (status === 422) {
            errors.value = fieldErrors(error)
        } else {
            errorMessage.value = 'No fue posible crear la cuenta. Revisa tu conexión e inténtalo de nuevo.'
        }
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
            <h1>Crear cuenta</h1>
            <p>Tus cuentas y movimientos son privados: solo tú los ves.</p>
        </header>

        <div class="field">
            <label for="register-name">Nombre</label>

            <input
                id="register-name"
                v-model="form.name"
                class="form-input"
                :class="{ 'input-error': errors.name }"
                type="text"
                autocomplete="name"
                placeholder="Ej: Ana Gómez"
                maxlength="100"
            />

            <span v-if="errors.name" class="field-error">{{ errors.name }}</span>
        </div>

        <div class="field">
            <label for="register-email">Correo</label>

            <input
                id="register-email"
                v-model="form.email"
                class="form-input"
                :class="{ 'input-error': errors.email }"
                type="email"
                autocomplete="email"
                placeholder="tu@correo.com"
                maxlength="150"
            />

            <span v-if="errors.email" class="field-error">{{ errors.email }}</span>
        </div>

        <div class="field">
            <label for="register-password">Contraseña</label>

            <div class="password-field">
                <input
                    id="register-password"
                    v-model="form.password"
                    class="form-input"
                    :class="{ 'input-error': errors.password }"
                    :type="showPassword ? 'text' : 'password'"
                    autocomplete="new-password"
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

            <ul class="password-rules">
                <li
                    v-for="rule in passwordRules"
                    :key="rule.label"
                    :class="{ ok: rule.ok }"
                >
                    {{ rule.ok ? '✓' : '•' }} {{ rule.label }}
                </li>
            </ul>

            <span v-if="errors.password" class="field-error">{{ errors.password }}</span>
        </div>

        <div class="field">
            <label for="register-password-confirmation">Confirmar contraseña</label>

            <input
                id="register-password-confirmation"
                v-model="form.password_confirmation"
                class="form-input"
                :class="{ 'input-error': errors.password_confirmation }"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
            />

            <span v-if="errors.password_confirmation" class="field-error">
                {{ errors.password_confirmation }}
            </span>

            <span v-else-if="passwordsMatch" class="field-ok">
                ✓ Las contraseñas coinciden
            </span>
        </div>

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
            <template v-else-if="authStore.loading">Creando cuenta...</template>
            <template v-else>Crear cuenta</template>
        </button>

        <p class="auth-switch">
            ¿Ya tienes cuenta?

            <button
                type="button"
                class="link-btn"
                @click="emit('switch', form.email)"
            >
                Inicia sesión
            </button>
        </p>
    </form>
</template>

<style scoped src="../styles/auth-form.css"></style>
