<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
    email: '',
    password: '',
    remember: false,
})

const errorMessage = ref('')

const submit = async () => {
    errorMessage.value = ''

    try {
        await authStore.login(form)

        await router.push({name: 'dashboard',})
    } catch (error) {
        if (error.response?.status === 422) {
            errorMessage.value =
                error.response.data?.message ??
                'Las credenciales no son correctas.'
        } else {
            errorMessage.value =
                'No fue posible iniciar sesión.'
        }
    }
}
</script>

<template>
    <div class="login-page">
        <div class="card login-card">
        <h1>Iniciar sesión</h1>

        <form @submit.prevent="submit">
            <div class="field">
                <label>Email</label>

                <input
                    class="form-input"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    required
                />
            </div>

            <div class="field">
                <label>Contraseña</label>

                <input
                    class="form-input"
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    required
                />
            </div>

            <div>
                <label>
                    <input
                        v-model="form.remember"
                        type="checkbox"
                    />

                    Recordarme
                </label>
            </div>

            <button
                class="submit-btn"
                type="submit"
                :disabled="authStore.loading"
            >
                {{
                    authStore.loading
                        ? 'Ingresando...'
                        : 'Iniciar sesión'
                }}
            </button>
        </form>

        <p v-if="errorMessage" class="text-danger">
            {{ errorMessage }}
        </p>
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
    max-width: 400px;
    padding: 32px;
}

h1 {
    margin: 0 0 24px;
    font-size: 24px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 16px;
}

label {
    font-size: 13px;
    font-weight: 500;
}

.submit-btn {
    width: 100%;
    min-height: 42px;
    margin-top: 8px;
    border: none;
    border-radius: var(--radius-md);
    background: var(--color-primary);
    color: #fff;
    font-weight: 600;
}

.submit-btn:hover:not(:disabled) {
    background: var(--color-primary-hover);
}

.submit-btn:disabled {
    opacity: 0.6;
}
</style>