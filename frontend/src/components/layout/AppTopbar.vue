<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../modules/Auth/stores/authStore'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

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

const goToSettings = () => {
    closeUserMenu()

    router.push({
        name: 'settings',
    })
}

const logout = async () => {
    closeUserMenu()

    await authStore.logout()

    router.push({
        name: 'login',
    })
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

            <div class="user-container">
                <button
                    class="user-trigger"
                    type="button"
                    @click.stop="toggleUserMenu"
                >
                    <div class="user-avatar">
                        {{ userInitials }}
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
                            {{ userInitials }}
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

<style scoped>
.topbar {
    min-height: 82px;
    padding: 0 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
}

.page-heading {
    display: flex;
    flex-direction: column;
}

.page-heading h1 {
    margin: 0;
    color: #111827;
    font-size: 20px;
    font-weight: 700;
}

.page-heading span {
    margin-top: 4px;
    color: #6b7280;
    font-size: 12px;
}

.topbar-actions {
    display: flex;
    align-items: center;
    gap: 18px;
}

.notification-button {
    position: relative;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 9px;
    background: transparent;
    color: #6b7280;
    cursor: pointer;
}

.notification-button:hover {
    background: #f3f4f6;
    color: #111827;
}

.notification-button svg {
    width: 19px;
    height: 19px;
}

.notification-dot {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #2563eb;
}

.separator {
    width: 1px;
    height: 32px;
    background: #e5e7eb;
}

.user-container {
    position: relative;
}

.user-trigger {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 5px 8px 5px 5px;
    border: 0;
    border-radius: 10px;
    background: transparent;
    cursor: pointer;
}

.user-trigger:hover {
    background: #f9fafb;
}

.user-avatar,
.dropdown-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #dbeafe;
    color: #1d4ed8;
    font-weight: 700;
}

.user-avatar {
    width: 38px;
    height: 38px;
    font-size: 12px;
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 130px;
}

.user-info strong {
    color: #111827;
    font-size: 12px;
    font-weight: 600;
}

.user-info span {
    margin-top: 2px;
    color: #6b7280;
    font-size: 10px;
}

.chevron {
    width: 15px;
    height: 15px;
    color: #9ca3af;
    transition: transform 0.2s ease;
}

.chevron.open {
    transform: rotate(180deg);
}

.user-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 250px;
    padding: 8px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow:
        0 15px 30px rgba(15, 23, 42, 0.08),
        0 4px 8px rgba(15, 23, 42, 0.04);
    z-index: 100;
}

.dropdown-user {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
}

.dropdown-avatar {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    font-size: 11px;
}

.dropdown-user strong,
.dropdown-user span {
    display: block;
}

.dropdown-user strong {
    color: #111827;
    font-size: 12px;
}

.dropdown-user span {
    margin-top: 3px;
    color: #9ca3af;
    font-size: 10px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 170px;
}

.dropdown-divider {
    height: 1px;
    margin: 5px 0;
    background: #f0f1f3;
}

.dropdown-item {
    width: 100%;
    min-height: 40px;
    padding: 0 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #4b5563;
    font-size: 11px;
    text-align: left;
    cursor: pointer;
}

.dropdown-item:hover {
    background: #f3f4f6;
    color: #111827;
}

.dropdown-item.logout {
    color: #dc2626;
}

.dropdown-item.logout:hover {
    background: #fef2f2;
}

@media (max-width: 700px) {
    .topbar {
        padding: 0 18px;
    }

    .user-info,
    .separator,
    .chevron {
        display: none;
    }
}
</style>