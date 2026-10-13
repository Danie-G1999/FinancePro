<script setup>
import AppSidebar from '../components/layout/AppSidebar.vue'
import AppTopbar from '../components/layout/AppTopbar.vue'
</script>

<template>
    <div class="app-layout">
        <AppSidebar />

        <div class="app-main">
            <AppTopbar />

            <main class="app-content">
                <router-view />
            </main>
        </div>
    </div>
</template>

<style scoped>
.app-layout {
    min-height: 100vh;
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
    padding: 28px;
    overflow-x: hidden;
}
</style>