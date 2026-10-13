<script setup>
import { formatMoney } from '../../../utils/money'

defineProps({
    transaction: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['close'])

function formatDate(value) {
    const [year, month, day] = value.split('-')

    return `${day}/${month}/${year}`
}

function formatDateTime(value) {
    return new Intl.DateTimeFormat('es-CO', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value))
}
</script>

<template>
    <div
        class="modal-overlay"
        @click.self="emit('close')"
    >
        <div class="modal" role="dialog" aria-modal="true">

            <header class="modal-header">
                <div>
                    <span
                        class="type-badge"
                        :class="transaction.type"
                    >
                        {{ transaction.type_label }}
                    </span>

                    <h2>{{ transaction.description }}</h2>
                </div>

                <button
                    type="button"
                    class="close-button"
                    aria-label="Cerrar"
                    @click="emit('close')"
                >
                    ×
                </button>
            </header>

            <strong
                class="amount"
                :class="transaction.type"
            >
                {{ transaction.type === 'income' ? '+' : '-' }}{{
                    formatMoney(transaction.amount, transaction.currency)
                }}
            </strong>

            <dl class="details">
                <div>
                    <dt>Fecha</dt>
                    <dd>{{ formatDate(transaction.date) }}</dd>
                </div>

                <div>
                    <dt>
                        {{
                            transaction.type === 'income'
                                ? 'Cuenta de destino'
                                : 'Cuenta de origen'
                        }}
                    </dt>
                    <dd>{{ transaction.account_name }}</dd>
                </div>

                <div>
                    <dt>Moneda</dt>
                    <dd>{{ transaction.currency }}</dd>
                </div>

                <div v-if="transaction.debt_name">
                    <dt>Pago de deuda</dt>
                    <dd>{{ transaction.debt_name }}</dd>
                </div>

                <div>
                    <dt>Registrado</dt>
                    <dd>{{ formatDateTime(transaction.created_at) }}</dd>
                </div>

                <div class="full">
                    <dt>Notas</dt>
                    <dd>{{ transaction.notes || 'Sin notas' }}</dd>
                </div>
            </dl>

            <div class="modal-actions">
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="emit('close')"
                >
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</template>

<style scoped src="../styles/transaction-detail-modal.css"></style>
