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

        router.push('/dashboard')
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
    <div>
        <h1>Iniciar sesión</h1>

        <form @submit.prevent="submit">
            <div>
                <label>Email</label>

                <input
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    required
                />
            </div>

            <div>
                <label>Contraseña</label>

                <input
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

        <p v-if="errorMessage">
            {{ errorMessage }}
        </p>
    </div>
</template>