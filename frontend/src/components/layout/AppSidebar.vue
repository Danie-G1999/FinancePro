<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const collapsed = ref(false)

const navigation = [
    {
        label: 'Principal',
        items: [
            {
                name: 'Dashboard',
                route: '/dashboard',
                icon: 'dashboard',
            },
        ],
    },
    {
        label: 'Finanzas',
        items: [
            {
                name: 'Movimientos',
                route: '/movimientos',
                icon: 'movement',
            },
            {
                name: 'Ingresos',
                route: '/ingresos',
                icon: 'income',
            },
            {
                name: 'Gastos',
                route: '/gastos',
                icon: 'expense',
            },
            {
                name: 'Cuentas',
                route: '/cuentas',
                icon: 'account',
            },
            {
                name: 'Deudas',
                route: '/deudas',
                icon: 'debt',
            },
            {
                name: 'Presupuestos',
                route: '/presupuestos',
                icon: 'budget',
            },
        ],
    },
    {
        label: 'Análisis',
        items: [
            {
                name: 'Reportes',
                route: '/reportes',
                icon: 'report',
            },
        ],
    },
]

const isActive = (item) => {
    return route.path === item.route
}
</script>

<template>
    <aside
        class="sidebar"
        :class="{ 'sidebar-collapsed': collapsed }"
    >
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-icon">
                    F
                </div>

                <div
                    v-if="!collapsed"
                    class="brand-content"
                >
                    <strong>FinancePro</strong>
                    <span>Finanzas personales</span>
                </div>
            </div>

            <button
                class="collapse-button"
                type="button"
                @click="collapsed = !collapsed"
            >
                <svg
                    v-if="!collapsed"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M15 18l-6-6 6-6" />
                </svg>

                <svg
                    v-else
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M9 18l6-6-6-6" />
                </svg>
            </button>
        </div>

        <nav class="sidebar-navigation">
            <div
                v-for="section in navigation"
                :key="section.label"
                class="navigation-section"
            >
                <span
                    v-if="!collapsed"
                    class="section-title"
                >
                    {{ section.label }}
                </span>

                <router-link
                    v-for="item in section.items"
                    :key="item.route"
                    :to="item.route"
                    class="navigation-item"
                    :class="{ active: isActive(item) }"
                    :title="collapsed ? item.name : undefined"
                >
                    <span class="navigation-icon">
                        <svg
                            v-if="item.icon === 'dashboard'"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="7" height="7" rx="1" />
                        </svg>

                        <svg
                            v-else-if="item.icon === 'movement'"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M7 7h13" />
                            <path d="M17 4l3 3-3 3" />
                            <path d="M17 17H4" />
                            <path d="M7 14l-3 3 3 3" />
                        </svg>

                        <svg
                            v-else-if="item.icon === 'income'"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M12 19V5" />
                            <path d="M6 11l6-6 6 6" />
                        </svg>

                        <svg
                            v-else-if="item.icon === 'expense'"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M12 5v14" />
                            <path d="M18 13l-6 6-6-6" />
                        </svg>

                        <svg
                            v-else-if="item.icon === 'account'"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <path d="M3 10h18" />
                            <path d="M7 15h4" />
                        </svg>

                        <svg
                            v-else-if="item.icon === 'debt'"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M4 7h16" />
                            <path d="M4 12h16" />
                            <path d="M4 17h10" />
                            <circle cx="18" cy="17" r="2" />
                        </svg>

                        <svg
                            v-else-if="item.icon === 'budget'"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M4 19V5" />
                            <path d="M4 19h16" />
                            <path d="M8 16v-4" />
                            <path d="M12 16V8" />
                            <path d="M16 16v-6" />
                        </svg>

                        <svg
                            v-else-if="item.icon === 'report'"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M4 19V5" />
                            <path d="M4 19h16" />
                            <path d="M8 15l3-4 3 2 5-6" />
                        </svg>
                    </span>

                    <span
                        v-if="!collapsed"
                        class="navigation-label"
                    >
                        {{ item.name }}
                    </span>
                </router-link>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="version">
                <span v-if="!collapsed">FinancePro v1.0</span>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.sidebar {
    width: 260px;
    min-width: 260px;
    height: 100vh;
    position: sticky;
    top: 0;
    display: flex;
    flex-direction: column;
    background: #111827;
    color: #9ca3af;
    transition:
        width 0.2s ease,
        min-width 0.2s ease;
    z-index: 30;
}

.sidebar-collapsed {
    width: 78px;
    min-width: 78px;
}

.sidebar-header {
    height: 82px;
    padding: 0 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.brand-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    background: #2563eb;
    color: white;
    font-size: 20px;
    font-weight: 800;
}

.brand-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.brand-content strong {
    color: #ffffff;
    font-size: 16px;
    line-height: 1.2;
}

.brand-content span {
    margin-top: 3px;
    color: #6b7280;
    font-size: 10px;
}

.collapse-button {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: #6b7280;
    cursor: pointer;
}

.collapse-button:hover {
    background: rgba(255, 255, 255, 0.07);
    color: #ffffff;
}

.collapse-button svg {
    width: 17px;
    height: 17px;
}

.sidebar-navigation {
    flex: 1;
    overflow-y: auto;
    padding: 22px 12px;
}

.navigation-section {
    margin-bottom: 26px;
}

.section-title {
    display: block;
    padding: 0 12px 9px;
    color: #4b5563;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.navigation-item {
    min-height: 44px;
    margin-bottom: 3px;
    padding: 0 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-radius: 9px;
    color: #9ca3af;
    text-decoration: none;
    transition:
        background 0.15s ease,
        color 0.15s ease;
}

.navigation-item:hover {
    background: rgba(255, 255, 255, 0.06);
    color: #ffffff;
}

.navigation-item.active {
    background: #1d4ed8;
    color: #ffffff;
}

.navigation-icon {
    width: 20px;
    height: 20px;
    flex: 0 0 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.navigation-icon svg {
    width: 19px;
    height: 19px;
}

.navigation-label {
    font-size: 13px;
    font-weight: 500;
}

.sidebar-footer {
    padding: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.07);
}

.version {
    color: #4b5563;
    font-size: 10px;
}
</style>