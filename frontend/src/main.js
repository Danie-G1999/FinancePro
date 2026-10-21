import { createApp } from 'vue'
import { createPinia } from 'pinia'

import '@fontsource/inter/400.css'
import '@fontsource/inter/500.css'
import '@fontsource/inter/600.css'
import '@fontsource/inter/700.css'

import './style.css'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './modules/Auth/stores/authStore'

const app = createApp(App)

app.use(createPinia())
app.use(router)

// Sesión vencida mientras la app estaba abierta: vuelve al login sin dejar datos a la vista.
window.addEventListener('auth:expired', (event) => {
    const authStore = useAuthStore()

    if (!authStore.isAuthenticated) {
        return
    }

    authStore.clearSession()

    const current = router.currentRoute.value

    router.replace({
        name: 'login',
        query: {
            ...(current.meta.requiresAuth && { redirect: current.fullPath }),
            ...(event.detail?.reason === 'inactive' ? { reason: 'inactive' } : { expired: '1' }),
        },
    })
})

app.mount('#app')