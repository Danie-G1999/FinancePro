<script setup>
import { useAuthStore } from '../../Auth/stores/authStore'

defineProps({
    secondsLeft: {
        type: Number,
        required: true,
    },
})

const emit = defineEmits(['stay'])

const authStore = useAuthStore()
</script>

<template>
    <Teleport to="body">
        <div class="inactivity-overlay">
            <div
                class="inactivity-card"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="inactivity-title"
                aria-describedby="inactivity-text"
            >
                <span class="inactivity-icon" aria-hidden="true">⏳</span>

                <h2 id="inactivity-title">¿Sigues ahí?</h2>

                <p id="inactivity-text">
                    Por seguridad cerraremos tu sesión por inactividad en
                    <strong>{{ secondsLeft }} s</strong>.
                </p>

                <div class="inactivity-actions">
                    <button
                        type="button"
                        class="inactivity-btn inactivity-btn-ghost"
                        @click="authStore.logoutAndRedirect()"
                    >
                        Cerrar sesión
                    </button>

                    <button
                        type="button"
                        class="inactivity-btn inactivity-btn-primary"
                        autofocus
                        @click="emit('stay')"
                    >
                        Seguir conectado
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
/* Encima del recorrido guiado (2000) y debajo del aviso legal (3000). */
.inactivity-overlay {
    position: fixed;
    inset: 0;
    z-index: 2500;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 16px;

    background: rgba(15, 23, 42, 0.6);
}

.inactivity-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;

    width: 100%;
    max-width: 380px;
    padding: 28px 24px 22px;

    border-radius: 16px;

    background: var(--color-surface);

    box-shadow: 0 24px 48px rgba(15, 23, 42, 0.3);

    text-align: center;
}

.inactivity-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 52px;
    height: 52px;

    border-radius: 50%;

    background: var(--color-warning-light);

    font-size: 24px;
}

.inactivity-card h2 {
    color: var(--color-text-primary);

    font-size: 18px;
    font-weight: 700;
}

.inactivity-card p {
    color: var(--color-text-secondary);

    font-size: 13px;
    line-height: 1.5;
}

.inactivity-actions {
    display: flex;
    gap: 10px;

    width: 100%;
    margin-top: 8px;
}

.inactivity-btn {
    flex: 1;
    min-height: 40px;

    border: 1px solid transparent;
    border-radius: var(--radius-md);

    font-size: 13px;
    font-weight: 600;
}

.inactivity-btn-primary {
    background: var(--color-primary);
    color: #fff;
}

.inactivity-btn-primary:hover {
    background: var(--color-primary-hover);
}

.inactivity-btn-ghost {
    border-color: var(--color-border);

    background: transparent;
    color: var(--color-text-secondary);
}

.inactivity-btn-ghost:hover {
    background: var(--color-surface-hover);
}
</style>
