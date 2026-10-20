<script setup>
import { computed } from 'vue'

import BaseModal from '../../../components/common/BaseModal.vue'
import { LEGAL_DOCUMENTS } from '../utils/legalDocuments'
import PrivacyContent from './PrivacyContent.vue'
import TermsContent from './TermsContent.vue'

const props = defineProps({
    // terms | privacy
    document: {
        type: String,
        required: true,
    },

    // En el registro, el botón "Acepto" marca la casilla correspondiente.
    acceptable: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close', 'accept'])

const meta = computed(() => LEGAL_DOCUMENTS[props.document])

const content = computed(() => (props.document === 'terms' ? TermsContent : PrivacyContent))

function accept() {
    emit('accept', props.document)
    emit('close')
}
</script>

<template>
    <BaseModal
        :title="meta.title"
        max-width="760px"
        @close="emit('close')"
    >
        <component :is="content" />

        <footer class="legal-modal-actions">
            <button
                type="button"
                class="legal-btn legal-btn-ghost"
                @click="emit('close')"
            >
                Cerrar
            </button>

            <button
                v-if="acceptable"
                type="button"
                class="legal-btn legal-btn-primary"
                @click="accept"
            >
                {{ document === 'terms' ? 'Acepto los términos' : 'Autorizo el tratamiento' }}
            </button>
        </footer>
    </BaseModal>
</template>

<style scoped>
.legal-modal-actions {
    position: sticky;
    bottom: -24px;

    display: flex;
    justify-content: flex-end;
    gap: 10px;

    margin: 20px -24px -24px;
    padding: 14px 24px;

    border-top: 1px solid var(--color-border);

    background: var(--color-surface);
}

.legal-btn {
    min-height: 40px;
    padding: 0 16px;

    border: 1px solid transparent;
    border-radius: var(--radius-md);

    font-size: 13px;
    font-weight: 600;
}

.legal-btn-primary {
    background: var(--color-primary);
    color: #fff;
}

.legal-btn-primary:hover {
    background: var(--color-primary-hover);
}

.legal-btn-ghost {
    border-color: var(--color-border);

    background: transparent;
    color: var(--color-text-secondary);
}

.legal-btn-ghost:hover {
    background: var(--color-surface-hover);
}

@media (max-width: 480px) {
    .legal-modal-actions {
        bottom: -16px;

        margin: 16px -16px -16px;
        padding: 12px 16px;
    }

    .legal-btn {
        flex: 1;
    }
}
</style>
