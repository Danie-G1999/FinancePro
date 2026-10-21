<script setup>
import { computed, nextTick, reactive, ref } from 'vue'

import { useAuthStore } from '../../Auth/stores/authStore'
import { profileService } from '../services/profileService'
import { showSuccess } from '../../../utils/alerts'

const authStore = useAuthStore()

// Las cuentas creadas con Google no tienen contraseña: la crean sin escribir una actual.
const hasPassword = computed(() => authStore.user?.has_password !== false)

const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
})

// La contraseña guardada no se puede mostrar (solo existe su hash); el ojo muestra lo que escribes.
const visible = reactive({
    current_password: false,
    password: false,
    password_confirmation: false,
})

const loading = ref(false)
const errors = ref({})
const errorMessage = ref('')

// Por defecto solo se ve el resumen; el formulario se abre con "Cambiar contraseña".
const editing = ref(false)

// Solo se conoce la fecha del último cambio: la contraseña y su hash nunca llegan al navegador.
const changedAt = computed(() => {
    const value = authStore.user?.password_changed_at

    return value ? new Date(value) : null
})

const changedAtExact = computed(() =>
    changedAt.value
        ? new Intl.DateTimeFormat('es-CO', { dateStyle: 'long', timeStyle: 'short' }).format(changedAt.value)
        : ''
)

const changedAtRelative = computed(() => {
    if (!changedAt.value) {
        return ''
    }

    const seconds = Math.round((changedAt.value.getTime() - Date.now()) / 1000)
    const units = [
        ['year', 60 * 60 * 24 * 365],
        ['month', 60 * 60 * 24 * 30],
        ['week', 60 * 60 * 24 * 7],
        ['day', 60 * 60 * 24],
        ['hour', 60 * 60],
        ['minute', 60],
    ]
    const formatter = new Intl.RelativeTimeFormat('es', { numeric: 'auto' })

    for (const [unit, size] of units) {
        if (Math.abs(seconds) >= size) {
            return formatter.format(Math.round(seconds / size), unit)
        }
    }

    return 'hace un momento'
})

async function openForm() {
    reset()
    editing.value = true

    await nextTick()
    document.getElementById(hasPassword.value ? 'password-current' : 'password-new')?.focus()
}

function closeForm() {
    reset()
    editing.value = false
}

// Mismas reglas que valida el backend.
const rules = computed(() => [
    { label: 'Mínimo 8 caracteres', ok: form.password.length >= 8 },
    { label: 'Al menos una letra', ok: /\p{L}/u.test(form.password) },
    { label: 'Al menos un número', ok: /\d/.test(form.password) },
    ...(hasPassword.value
        ? [{ label: 'Distinta de la actual', ok: form.password !== '' && form.password !== form.current_password }]
        : []),
])

const passwordsMatch = computed(
    () => form.password_confirmation !== '' && form.password === form.password_confirmation
)

const canSubmit = computed(
    () =>
        !loading.value &&
        (!hasPassword.value || form.current_password !== '') &&
        rules.value.every((rule) => rule.ok) &&
        passwordsMatch.value
)

function reset() {
    Object.assign(form, { current_password: '', password: '', password_confirmation: '' })
    Object.assign(visible, { current_password: false, password: false, password_confirmation: false })
    errors.value = {}
    errorMessage.value = ''
}

async function submit() {
    if (!canSubmit.value) {
        return
    }

    loading.value = true
    errors.value = {}
    errorMessage.value = ''

    try {
        const response = await profileService.updatePassword({
            ...(hasPassword.value && { current_password: form.current_password }),
            password: form.password,
            password_confirmation: form.password_confirmation,
        })

        closeForm()

        // Actualiza "has_password" y la fecha del último cambio.
        await authStore.fetchUser()

        showSuccess(response.message)
    } catch (error) {
        const data = error.response?.data

        if (error.response?.status === 422) {
            errors.value = Object.fromEntries(
                Object.entries(data?.errors ?? {}).map(([field, messages]) => [field, messages[0]])
            )
        } else {
            errorMessage.value = data?.message ?? 'No fue posible actualizar la contraseña.'
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <!-- RESUMEN -->

    <div
        v-if="!editing"
        class="password-summary"
    >
        <div class="password-summary-info">
            <span class="form-label">Contraseña</span>

            <span
                v-if="hasPassword"
                class="password-dots"
                aria-label="Contraseña configurada"
            >
                ••••••••••
            </span>

            <span v-else class="password-none">Sin contraseña</span>

            <span class="form-help">
                <template v-if="!hasPassword">
                    Tu cuenta se creó con Google. Crea una contraseña para entrar también con tu correo.
                </template>

                <template v-else-if="changedAt">
                    Actualizada <time :datetime="changedAt.toISOString()" :title="changedAtExact">{{ changedAtRelative }}</time>.
                </template>

                <template v-else>
                    Aún no tenemos registro de cuándo la cambiaste por última vez.
                </template>
            </span>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            @click="openForm"
        >
            {{ hasPassword ? 'Cambiar contraseña' : 'Crear contraseña' }}
        </button>
    </div>


    <!-- FORMULARIO -->

    <form
        v-else
        class="password-form"
        novalidate
        @submit.prevent="submit"
    >
        <p
            v-if="!hasPassword"
            class="password-notice"
        >
            Tu cuenta se creó con Google y todavía no tiene contraseña. Crea una para poder entrar
            también con tu correo.
        </p>

        <div
            v-if="hasPassword"
            class="form-group form-group-full"
        >
            <label for="password-current" class="form-label">
                Contraseña actual
                <span class="required">*</span>
            </label>

            <div class="password-field">
                <input
                    id="password-current"
                    v-model="form.current_password"
                    :type="visible.current_password ? 'text' : 'password'"
                    class="form-input"
                    :class="{ 'input-error': errors.current_password }"
                    autocomplete="current-password"
                    :disabled="loading"
                />

                <button
                    type="button"
                    class="toggle-password"
                    :aria-label="visible.current_password ? 'Ocultar contraseña actual' : 'Mostrar contraseña actual'"
                    :aria-pressed="visible.current_password"
                    @click="visible.current_password = !visible.current_password"
                >
                    {{ visible.current_password ? 'Ocultar' : 'Mostrar' }}
                </button>
            </div>

            <span v-if="errors.current_password" class="form-error">
                {{ errors.current_password }}
            </span>

            <span v-else class="form-help">
                Por seguridad no podemos mostrarte la contraseña guardada: se almacena cifrada.
                Usa "Mostrar" para revisar lo que escribes.
            </span>
        </div>

        <div class="form-group">
            <label for="password-new" class="form-label">
                {{ hasPassword ? 'Nueva contraseña' : 'Contraseña' }}
                <span class="required">*</span>
            </label>

            <div class="password-field">
                <input
                    id="password-new"
                    v-model="form.password"
                    :type="visible.password ? 'text' : 'password'"
                    class="form-input"
                    :class="{ 'input-error': errors.password }"
                    autocomplete="new-password"
                    :disabled="loading"
                />

                <button
                    type="button"
                    class="toggle-password"
                    :aria-label="visible.password ? 'Ocultar nueva contraseña' : 'Mostrar nueva contraseña'"
                    :aria-pressed="visible.password"
                    @click="visible.password = !visible.password"
                >
                    {{ visible.password ? 'Ocultar' : 'Mostrar' }}
                </button>
            </div>

            <ul class="password-rules">
                <li
                    v-for="rule in rules"
                    :key="rule.label"
                    :class="{ ok: rule.ok }"
                >
                    {{ rule.ok ? '✓' : '•' }} {{ rule.label }}
                </li>
            </ul>

            <span v-if="errors.password" class="form-error">
                {{ errors.password }}
            </span>
        </div>

        <div class="form-group">
            <label for="password-confirmation" class="form-label">
                Confirmar {{ hasPassword ? 'nueva ' : '' }}contraseña
                <span class="required">*</span>
            </label>

            <div class="password-field">
                <input
                    id="password-confirmation"
                    v-model="form.password_confirmation"
                    :type="visible.password_confirmation ? 'text' : 'password'"
                    class="form-input"
                    autocomplete="new-password"
                    :disabled="loading"
                />

                <button
                    type="button"
                    class="toggle-password"
                    :aria-label="visible.password_confirmation ? 'Ocultar confirmación' : 'Mostrar confirmación'"
                    :aria-pressed="visible.password_confirmation"
                    @click="visible.password_confirmation = !visible.password_confirmation"
                >
                    {{ visible.password_confirmation ? 'Ocultar' : 'Mostrar' }}
                </button>
            </div>

            <span
                v-if="form.password_confirmation && !passwordsMatch"
                class="form-error"
            >
                Las contraseñas no coinciden.
            </span>

            <span
                v-else-if="passwordsMatch"
                class="field-ok"
            >
                ✓ Las contraseñas coinciden
            </span>
        </div>

        <p
            v-if="errorMessage"
            class="form-error form-group-full"
            role="alert"
        >
            {{ errorMessage }}
        </p>

        <div class="form-actions">
            <span class="form-help actions-hint">
                Al cambiarla cerraremos tus sesiones abiertas en otros equipos.
            </span>

            <button
                type="button"
                class="btn btn-secondary"
                :disabled="loading"
                @click="closeForm"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="btn btn-primary"
                :disabled="!canSubmit"
            >
                {{ loading ? 'Guardando...' : hasPassword ? 'Actualizar contraseña' : 'Crear contraseña' }}
            </button>
        </div>
    </form>
</template>

<style scoped src="../styles/profile-form.css"></style>
<style scoped src="../styles/change-password-form.css"></style>
