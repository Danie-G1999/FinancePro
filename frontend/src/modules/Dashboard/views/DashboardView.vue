<script setup>
import { useAuthStore } from '../../Auth/stores/authStore'

const authStore = useAuthStore()
</script>

<template>
    <div>
        <h1>FinancePro</h1>

        <h2>Dashboard</h2>

        <p v-if="authStore.user">
            Bienvenido, {{ authStore.user.name }}
        </p>

        <p v-if="authStore.user">
            {{ authStore.user.email }}
        </p>
    </div>
</template>