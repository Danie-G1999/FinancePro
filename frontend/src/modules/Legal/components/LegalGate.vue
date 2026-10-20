<script setup>
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'

import { useAuthStore } from '../../Auth/stores/authStore'
import { LEGAL_DOCUMENTS } from '../utils/legalDocuments'
import PrivacyContent from './PrivacyContent.vue'
import TermsContent from './TermsContent.vue'

const router = useRouter()
const authStore = useAuthStore()

const CONTENT = { terms: TermsContent, privacy: PrivacyContent }

const LABELS = {
    terms: 'He leído y acepto los Términos y Condiciones.',
    privacy: 'Autorizo de manera previa, expresa e informada el tratamiento de mis datos personales conforme a la Política de Tratamiento de Datos (Ley 1581 de 2012).',
}

const pending = computed(() => authStore.user?.legal?.pending ?? [])

// Si ya había aceptado una versión anterior, se le explica que el documento cambió.
const isUpdate = computed(() =>
    pending.value.some((key) => authStore.user?.legal?.documents?.[key]?.accepted_version)
)

const checked = reactive({ terms: false, privacy: false })

const expanded = ref(null)

const saving = ref(false)

const errorMessage = ref('')

const canAccept = computed(() => pending.value.every((key) => checked[key]))

function toggle(key) {
    expanded.value = expanded.value === key ? null : key
}

async function accept() {
    if (!canAccept.value) {
        return
    }

    saving.value = true
    errorMessage.value = ''

    try {
        await authStore.acceptLegal(pending.value)
    } catch {
        errorMessage.value = 'No fue posible guardar tu aceptación. Revisa tu conexión e inténtalo de nuevo.'
    } finally {
        saving.value = false
    }
}

async function logout() {
    await authStore.logout()

    window.location.replace(router.resolve({ name: 'login' }).href)
}
</script>

<template>
    <Teleport to="body">
        <div class="gate-overlay">
            <div
                class="gate-card"
                role="dialog"
                aria-modal="true"
                aria-labelledby="gate-title"
            >
                <header class="gate-header">
                    <span class="gate-icon" aria-hidden="true">🛡️</span>

                    <div>
                        <h2 id="gate-title">
                            {{ isUpdate ? 'Actualizamos nuestros documentos' : 'Antes de continuar' }}
                        </h2>

                        <p>
                            {{
                                isUpdate
                                    ? 'Para seguir usando FinancePro necesitamos que revises y aceptes la nueva versión.'
                                    : 'Para proteger tu información y cumplir la ley colombiana de protección de datos, necesitamos tu autorización.'
                            }}
                        </p>
                    </div>
                </header>

                <div class="gate-body">
                    <section
                        v-for="key in pending"
                        :key="key"
                        class="gate-document"
                    >
                        <div class="gate-document-head">
                            <div>
                                <strong>{{ LEGAL_DOCUMENTS[key].title }}</strong>
                                <span>Versión {{ LEGAL_DOCUMENTS[key].version }}</span>
                            </div>

                            <button
                                type="button"
                                class="gate-read"
                                :aria-expanded="expanded === key"
                                @click="toggle(key)"
                            >
                                {{ expanded === key ? 'Ocultar' : 'Leer documento' }}
                            </button>
                        </div>

                        <div
                            v-if="expanded === key"
                            class="gate-document-content"
                        >
                            <component :is="CONTENT[key]" />
                        </div>

                        <label class="gate-check">
                            <input
                                v-model="checked[key]"
                                type="checkbox"
                            />

                            <span>{{ LABELS[key] }}</span>
                        </label>
                    </section>

                    <p
                        v-if="errorMessage"
                        class="gate-error"
                        role="alert"
                    >
                        {{ errorMessage }}
                    </p>
                </div>

                <footer class="gate-actions">
                    <button
                        type="button"
                        class="gate-btn gate-btn-ghost"
                        :disabled="saving"
                        @click="logout"
                    >
                        Cerrar sesión
                    </button>

                    <button
                        type="button"
                        class="gate-btn gate-btn-primary"
                        :disabled="!canAccept || saving"
                        @click="accept"
                    >
                        {{ saving ? 'Guardando...' : 'Aceptar y continuar' }}
                    </button>
                </footer>
            </div>
        </div>
    </Teleport>
</template>

<style scoped src="../styles/legal-gate.css"></style>
