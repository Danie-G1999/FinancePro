<script setup>
import AppSidebar from '../components/layout/AppSidebar.vue'
import AppTopbar from '../components/layout/AppTopbar.vue'
import { useLayoutStore } from '../components/layout/layoutStore'
import AppTour from '../modules/Onboarding/components/AppTour.vue'

const layout = useLayoutStore()
</script>

<template>
    <div class="app-layout">
        <AppSidebar />

        <!-- Fondo del menú móvil: tocarlo cierra el menú. -->
        <Transition name="backdrop-fade">
            <div
                v-if="layout.isMobile && layout.menuOpen"
                class="sidebar-backdrop"
                @click="layout.closeMenu()"
            ></div>
        </Transition>

        <div class="app-main">
            <AppTopbar />

            <main class="app-content">
                <router-view />
            </main>
        </div>

        <AppTour />
    </div>
</template>

<style scoped>
.app-layout {
    height: 100vh;
    height: 100dvh;
    overflow: hidden;
    display: flex;
    background: #f5f7fb;
}

.app-main {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.app-content {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    padding: 28px;
    overflow-x: hidden;
    overflow-y: auto;
}

.sidebar-backdrop {
    position: fixed;
    inset: 0;
    z-index: 999;
    background: rgba(15, 23, 42, 0.5);
}

.backdrop-fade-enter-active,
.backdrop-fade-leave-active {
    transition: opacity 0.25s ease;
}

.backdrop-fade-enter-from,
.backdrop-fade-leave-to {
    opacity: 0;
}

@media (max-width: 1024px) {
    .app-content {
        padding: 20px;
    }
}

@media (max-width: 640px) {
    .app-content {
        padding: 16px;
    }
}
</style>
