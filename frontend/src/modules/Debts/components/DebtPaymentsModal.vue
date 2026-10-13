<script setup>
import { computed, onMounted, ref } from 'vue'

import { debtService } from '../services/debtService'
import { formatMoney } from '../../../utils/money'

const props = defineProps({
    debt: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['close'])

const payments = ref([])

const loading = ref(true)

const error = ref('')

const paidTotal = computed(() =>
    payments.value.reduce((sum, payment) => sum + Number(payment.amount), 0)
)

function formatDate(value) {
    const [year, month, day] = value.split('-')

    return `${day}/${month}/${year}`
}

onMounted(async () => {
    try {
        const response = await debtService.getPayments(props.debt.id)

        payments.value = response.data
    } catch (err) {
        error.value =
            err.response?.data?.message ??
            'No fue posible cargar los pagos.'
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div
        class="modal-overlay"
        @click.self="emit('close')"
    >
        <div class="modal" role="dialog" aria-modal="true">

            <header class="modal-header">
                <div>
                    <h2>Pagos de la deuda</h2>

                    <p>{{ debt.name }}</p>
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

            <div class="summary">
                <div>
                    <span>Valor total</span>
                    <strong>
                        {{ formatMoney(debt.total_amount, debt.currency) }}
                    </strong>
                </div>

                <div>
                    <span>Pagado</span>
                    <strong class="paid">
                        {{ formatMoney(debt.paid_amount, debt.currency) }}
                    </strong>
                </div>

                <div>
                    <span>Saldo pendiente</span>
                    <strong class="pending">
                        {{ formatMoney(debt.remaining_balance, debt.currency) }}
                    </strong>
                </div>

                <div>
                    <span>Cuotas</span>
                    <strong>
                        {{ debt.paid_installments }} de {{ debt.installments }}
                    </strong>
                </div>
            </div>

            <div
                v-if="loading"
                class="state"
            >
                Cargando pagos...
            </div>

            <div
                v-else-if="error"
                class="state state-error"
            >
                {{ error }}
            </div>

            <div
                v-else-if="payments.length === 0"
                class="state"
            >
                Aún no has registrado pagos para esta deuda.
                Regístralos desde el módulo de gastos.
            </div>

            <div
                v-else
                class="payments-scroll"
            >
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cuenta</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="payment in payments"
                            :key="payment.id"
                        >
                            <td>{{ formatDate(payment.spent_at) }}</td>

                            <td>{{ payment.account_name }}</td>

                            <td class="text-right amount">
                                {{ formatMoney(payment.amount, debt.currency) }}
                            </td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="2">Total pagado</td>

                            <td class="text-right amount">
                                {{ formatMoney(paidTotal, debt.currency) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</template>

<style scoped src="../styles/debt-payments-modal.css"></style>
