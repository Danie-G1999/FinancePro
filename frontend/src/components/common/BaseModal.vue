<script setup>
import { onBeforeUnmount, onMounted } from 'vue'

const props = defineProps({
    title: {
        type: String,
        required: true,
    },

    subtitle: {
        type: String,
        default: '',
    },

    maxWidth: {
        type: String,
        default: '480px',
    },

    // Evita cerrar con clic fuera o Escape mientras hay una acción en curso.
    persistent: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close'])

function requestClose() {
    if (!props.persistent) {
        emit('close')
    }
}

function handleKeydown(event) {
    if (event.key === 'Escape') {
        requestClose()
    }
}

onMounted(() => document.addEventListener('keydown', handleKeydown))

onBeforeUnmount(() => document.removeEventListener('keydown', handleKeydown))
</script>

<template>
    <div
        class="modal-overlay"
        @click.self="requestClose"
    >
        <div
            class="modal"
            role="dialog"
            aria-modal="true"
            :aria-label="title"
            :style="{ maxWidth }"
        >
            <header class="modal-header">
                <div>
                    <h2>{{ title }}</h2>

                    <p v-if="subtitle">{{ subtitle }}</p>
                </div>

                <button
                    type="button"
                    class="close-button"
                    aria-label="Cerrar"
                    :disabled="persistent"
                    @click="requestClose"
                >
                    ×
                </button>
            </header>

            <div class="modal-body">
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped src="./base-modal.css"></style>
