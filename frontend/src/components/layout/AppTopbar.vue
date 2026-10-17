<script setup>
import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../modules/Auth/stores/authStore'
import { useTourStore } from '../../modules/Onboarding/stores/tourStore'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const tourStore = useTourStore()

const showUserMenu = ref(false)

const pageTitles = {
    dashboard: {
        title: 'Dashboard',
        subtitle: 'Resumen de tus finanzas',
    },

    cuentas: {
        title: 'Cuentas',
        subtitle: 'Administra tus cuentas financieras',
    },

    movimientos: {
        title: 'Movimientos',
        subtitle: 'Consulta tus movimientos financieros',
    },

    ingresos: {
        title: 'Ingresos',
        subtitle: 'Controla tus fuentes de ingreso',
    },

    gastos: {
        title: 'Gastos',
        subtitle: 'Controla tus gastos',
    },

    deudas: {
        title: 'Deudas',
        subtitle: 'Controla tus obligaciones financieras',
    },

    presupuestos: {
        title: 'Presupuestos',
        subtitle: 'Planifica tus gastos',
    },

    reportes: {
        title: 'Reportes',
        subtitle: 'Analiza tu información financiera',
    },
}

const currentPage = computed(() => {
    return (
        pageTitles[route.name] ?? {
            title: 'FinancePro',
            subtitle: 'Finanzas personales',
        }
    )
})

const userInitials = computed(() => {
    if (!authStore.user?.name) {
        return 'U'
    }

    return authStore.user.name
        .split(' ')
        .slice(0, 2)
        .map((name) => name.charAt(0))
        .join('')
        .toUpperCase()
})

const photoFailed = ref(false)

const photoUrl = computed(() =>
    photoFailed.value ? null : authStore.user?.photo_url ?? null
)

// Una foto nueva debe intentarse de nuevo aunque la anterior haya fallado.
watch(
    () => authStore.user?.photo_url,
    () => {
        photoFailed.value = false
    }
)

const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value
}

const closeUserMenu = () => {
    showUserMenu.value = false
}

const goToProfile = () => {
    closeUserMenu()

    router.push({
        name: 'profile',
    })
}

const startTour = () => {
    closeUserMenu()

    tourStore.start()
}

const goToSettings = () => {
    closeUserMenu()

    router.push({
        name: 'settings',
    })
}

const logout = async () => {
    closeUserMenu()

    await authStore.logout()

    // Recarga completa: borra de memoria los datos del usuario que cerró sesión
    // para que quien entre después en esta pestaña no los vea.
    window.location.replace(router.resolve({ name: 'login' }).href)
}

const handleClickOutside = (event) => {
    const menu = document.querySelector('.user-container')

    if (menu && !menu.contains(event.target)) {
        closeUserMenu()
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
    <header class="topbar">
        <div class="page-heading">
            <h1>{{ currentPage.title }}</h1>

            <span>
                {{ currentPage.subtitle }}
            </span>
        </div>

        <div class="topbar-actions">
            <button
                class="notification-button"
                type="button"
                title="Notificaciones"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"
                    />

                    <path d="M10 21h4" />
                </svg>

                <span class="notification-dot"></span>
            </button>

            <div class="separator"></div>

            <div class="user-container" data-tour="user-menu">
                <button
                    class="user-trigger"
                    type="button"
                    @click.stop="toggleUserMenu"
                >
                    <div class="user-avatar">
                        <img
                            v-if="photoUrl"
                            :src="photoUrl"
                            alt="Foto de perfil"
                            @error="photoFailed = true"
                        />

                        <template v-else>
                            {{ userInitials }}
                        </template>
                    </div>

                    <div class="user-info">
                        <strong>
                            {{ authStore.user?.name ?? 'Usuario' }}
                        </strong>

                        <span>
                            {{ authStore.user?.email ?? '' }}
                        </span>
                    </div>

                    <svg
                        class="chevron"
                        :class="{ open: showUserMenu }"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </button>

                <div
                    v-if="showUserMenu"
                    class="user-dropdown"
                >
                    <div class="dropdown-user">
                        <div class="dropdown-avatar">
                            <img
                                v-if="photoUrl"
                                :src="photoUrl"
                                alt="Foto de perfil"
                                @error="photoFailed = true"
                            />

                            <template v-else>
                                {{ userInitials }}
                            </template>
                        </div>

                        <div>
                            <strong>
                                {{ authStore.user?.name }}
                            </strong>

                            <span>
                                {{ authStore.user?.email }}
                            </span>
                        </div>
                    </div>

                    <div class="dropdown-divider"></div>

                    <button
                        type="button"
                        class="dropdown-item"
                        @click="goToProfile"
                    >
                        <span>👤</span>
                        <span>Mi perfil</span>
                    </button>

                    <button
                        type="button"
                        class="dropdown-item"
                        @click="goToSettings"
                    >
                        <span>⚙</span>
                        <span>Configuración</span>
                    </button>

                    <button
                        type="button"
                        class="dropdown-item"
                        @click="startTour"
                    >
                        <span>🧭</span>
                        <span>Ver recorrido guiado</span>
                    </button>

                    <div class="dropdown-divider"></div>

                    <button
                        type="button"
                        class="dropdown-item logout"
                        @click="logout"
                    >
                        <span>↪</span>
                        <span>Cerrar sesión</span>
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped src="./app-topbar.css"></style>
