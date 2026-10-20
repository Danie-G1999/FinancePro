<script setup>
import { computed, reactive, ref } from 'vue'

import GoogleButton from './GoogleButton.vue'
import LegalModal from '../../Legal/components/LegalModal.vue'
import { useAuthStore } from '../stores/authStore'
import { fieldErrors, useLockout } from '../composables/useLockout'

const props = defineProps({
    initialEmail: {
        type: String,
        default: '',
    },

    // Solo se muestra si el backend tiene credenciales de Google configuradas.
    googleEnabled: {
        type: Boolean,
        default: false,
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
    // La ley exige autorización expresa: las casillas nunca vienen marcadas.
    accept_terms: false,
    accept_privacy: false,
})

const openDocument = ref(null)

function acceptFromModal(document) {
    form[document === 'terms' ? 'accept_terms' : 'accept_privacy'] = true
}

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

    if (!form.accept_terms) {
        errors.value.accept_terms = 'Debes aceptar los Términos y Condiciones.'
    }

    if (!form.accept_privacy) {
        errors.value.accept_privacy = 'Debes autorizar el tratamiento de tus datos personales.'
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
            accept_terms: form.accept_terms,
            accept_privacy: form.accept_privacy,
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

        <template v-if="googleEnabled">
            <GoogleButton label="Registrarme con Google" />

            <div class="auth-divider">
                <span>o con tu correo</span>
            </div>
        </template>

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

        <div class="legal-checks">
            <label class="legal-check">
                <input
                    v-model="form.accept_terms"
                    type="checkbox"
                />

                <span>
                    He leído y acepto los
                    <button
                        type="button"
                        class="link-btn"
                        @click.prevent="openDocument = 'terms'"
                    >
                        Términos y Condiciones
                    </button>.
                </span>
            </label>

            <span v-if="errors.accept_terms" class="field-error">{{ errors.accept_terms }}</span>

            <label class="legal-check">
                <input
                    v-model="form.accept_privacy"
                    type="checkbox"
                />

                <span>
                    Autorizo el tratamiento de mis datos personales conforme a la
                    <button
                        type="button"
                        class="link-btn"
                        @click.prevent="openDocument = 'privacy'"
                    >
                        Política de Tratamiento de Datos
                    </button>
                    (Ley 1581 de 2012).
                </span>
            </label>

            <span v-if="errors.accept_privacy" class="field-error">{{ errors.accept_privacy }}</span>
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
        <LegalModal
            v-if="openDocument"
            :document="openDocument"
            acceptable
            @accept="acceptFromModal"
            @close="openDocument = null"
        />
    </form>
</template>

<style scoped src="../styles/auth-form.css"></style>
