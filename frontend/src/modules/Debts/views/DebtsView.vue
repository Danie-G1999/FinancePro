<script setup>
import { onMounted, ref } from 'vue'

import DebtForm from '../components/DebtForm.vue'
import DebtPaymentsModal from '../components/DebtPaymentsModal.vue'
import { useDebtStore } from '../stores/debtStore'
import { formatMoney } from '../../../utils/money'
import {
    extractErrorMessage,
    showError,
    showSuccess,
    withLoading,
} from '../../../utils/alerts'

const debtStore = useDebtStore()

const showForm = ref(false)

const editingDebt = ref(null)

const debtToDelete = ref(null)

const deleting = ref(false)

const debtWithPayments = ref(null)

function formatDate(value) {
    if (!value) {
        return ''
    }

    const [year, month, day] = value.split('-')

    return `${day}/${month}/${year}`
}

function openCreate() {
    editingDebt.value = null
    showForm.value = true
}

function openEdit(debt) {
    editingDebt.value = debt
    showForm.value = true
}

async function handleSaveDebt(data) {
    const isEditing = editingDebt.value !== null

    try {
        await withLoading(
            isEditing ? 'Guardando cambios...' : 'Guardando deuda...',
            () =>
                isEditing
                    ? debtStore.updateDebt(editingDebt.value.id, data)
                    : debtStore.createDebt(data)
        )

        showForm.value = false
        editingDebt.value = null

        showSuccess(
            isEditing
                ? 'Deuda actualizada correctamente.'
                : 'Deuda creada correctamente.'
        )
    } catch (error) {
        showError(
            extractErrorMessage(
                error,
                'No fue posible guardar la deuda.'
            )
        )
    }
}

function handleCancel() {
    showForm.value = false
    editingDebt.value = null
}

function askDelete(debt) {
    debtToDelete.value = debt
}

async function confirmDelete() {
    deleting.value = true

    try {
        await withLoading('Eliminando deuda...', () =>
            debtStore.deleteDebt(debtToDelete.value.id)
        )

        debtToDelete.value = null

        showSuccess('Deuda eliminada correctamente.')
    } catch (error) {
        debtToDelete.value = null

        showError(
            extractErrorMessage(
                error,
                'No fue posible eliminar la deuda.'
            )
        )
    } finally {
        deleting.value = false
    }
}

onMounted(() => {
    debtStore.fetchDebts()
})
</script>

<template>
    <div class="debts-page">

        <!-- HEADER -->

        <header class="page-header">

            <div>
                <h1 class="page-title">
                    Deudas
                </h1>

                <p class="page-description">
                    Controla lo que debes y cuánto llevas pagado.
                </p>
            </div>

            <button
                v-if="!showForm"
                type="button"
                class="btn btn-primary"
                @click="openCreate"
            >
                + Nueva deuda
            </button>

        </header>


        <!-- FORM -->

        <section
            v-if="showForm"
            class="form-card"
        >

            <div class="form-card-header">

                <div>
                    <h2>
                        {{ editingDebt ? 'Editar deuda' : 'Nueva deuda' }}
                    </h2>

                    <p>
                        {{
                            editingDebt
                                ? 'Modifica los datos de la deuda.'
                                : 'Registra una nueva deuda.'
                        }}
                    </p>
                </div>

                <button
                    type="button"
                    class="close-button"
                    @click="handleCancel"
                >
                    ×
                </button>

            </div>

            <div class="form-card-body">

                <DebtForm
                    :key="editingDebt?.id ?? 'new'"
                    :debt="editingDebt"
                    @submit="handleSaveDebt"
                    @cancel="handleCancel"
                />

            </div>

        </section>


        <!-- LOADING -->

        <div
            v-if="debtStore.loading && !showForm"
            class="state-container"
        >
            <div class="loading-spinner"></div>

            <p>
                Cargando deudas...
            </p>
        </div>


        <!-- ERROR -->

        <div
            v-else-if="debtStore.error && !showForm"
            class="alert alert-error"
        >
            {{ debtStore.error }}
        </div>


        <!-- EMPTY -->

        <section
            v-else-if="
                !debtStore.loading &&
                debtStore.debts.length === 0 &&
                !showForm
            "
            class="empty-state"
        >

            <div class="empty-icon">
                $
            </div>

            <h2>
                No tienes deudas registradas
            </h2>

            <p>
                Registra tus deudas para llevar el control de
                tus pagos y saldos pendientes.
            </p>

            <button
                type="button"
                class="btn btn-primary"
                @click="openCreate"
            >
                Registrar mi primera deuda
            </button>

        </section>


        <!-- DEBTS -->

        <section
            v-else-if="debtStore.debts.length > 0 && !showForm"
            class="debts-grid"
        >

            <article
                v-for="debt in debtStore.debts"
                :key="debt.id"
                class="debt-card"
            >

                <div class="debt-card-header">

                    <div class="debt-icon">
                        $
                    </div>

                    <div class="debt-info">
                        <h3>
                            {{ debt.name }}
                        </h3>

                        <span>
                            {{ debt.type_label }}
                        </span>
                    </div>

                    <div class="debt-actions">
                        <button
                            type="button"
                            class="icon-button"
                            title="Ver pagos"
                            aria-label="Ver pagos de la deuda"
                            @click="debtWithPayments = debt"
                        >
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 11v5" />
                                <path d="M12 8h.01" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="icon-button"
                            title="Editar deuda"
                            aria-label="Editar deuda"
                            @click="openEdit(debt)"
                        >
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9" />
                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                            </svg>
                        </button>

                        <button
                            type="button"
                            class="icon-button danger"
                            title="Eliminar deuda"
                            aria-label="Eliminar deuda"
                            @click="askDelete(debt)"
                        >
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18" />
                                <path d="M8 6V4h8v2" />
                                <path d="M19 6l-1 14H6L5 6" />
                                <path d="M10 11v5M14 11v5" />
                            </svg>
                        </button>
                    </div>

                </div>


                <div class="debt-card-body">

                    <span class="balance-label">
                        Valor total
                    </span>

                    <strong class="balance">
                        {{ formatMoney(debt.total_amount, debt.currency) }}
                    </strong>

                    <span class="progress-text">
                        Saldo pendiente:
                        <strong>
                            {{ formatMoney(debt.remaining_balance, debt.currency) }}
                        </strong>
                    </span>

                    <span class="progress-text">
                        {{ debt.paid_installments }} de
                        {{ debt.installments }} cuotas pagadas
                    </span>

                    <span class="progress-text">
                        {{ debt.installments }}
                        {{ debt.installments === 1 ? 'cuota' : 'cuotas' }}
                        <template v-if="debt.monthly_payment">
                            de
                            {{ formatMoney(debt.monthly_payment, debt.currency) }}
                        </template>
                    </span>

                    <span class="progress-text">
                        {{ debt.payment_frequency_label }}:
                        {{ debt.second_payment_day ? 'días' : 'día' }}
                        {{ debt.payment_days_label }} de cada mes
                    </span>

                </div>


                <div class="debt-card-footer">

                    <span
                        v-if="debt.interest_rate"
                        class="debt-status active"
                    >
                        <span class="status-dot"></span>

                        {{ debt.interest_rate }}% interés
                    </span>

                    <span v-else class="debt-status inactive">
                        <span class="status-dot"></span>

                        Sin interés
                    </span>

                    <span
                        v-if="debt.due_date"
                        class="institution"
                    >
                        Vence {{ formatDate(debt.due_date) }}
                    </span>

                    <span
                        v-else-if="debt.creditor"
                        class="institution"
                    >
                        {{ debt.creditor }}
                    </span>

                </div>

            </article>

        </section>


        <!-- PAYMENTS -->

        <DebtPaymentsModal
            v-if="debtWithPayments"
            :debt="debtWithPayments"
            @close="debtWithPayments = null"
        />


        <!-- DELETE CONFIRMATION -->

        <div
            v-if="debtToDelete"
            class="modal-overlay"
            @click.self="!deleting && (debtToDelete = null)"
        >
            <div class="modal" role="dialog" aria-modal="true">
                <h2>
                    Eliminar deuda
                </h2>

                <p>
                    ¿Seguro que deseas eliminar la deuda
                    <strong>{{ debtToDelete.name }}</strong>?
                    Esta acción no se puede deshacer.
                </p>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        :disabled="deleting"
                        @click="debtToDelete = null"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger"
                        :disabled="deleting"
                        @click="confirmDelete"
                    >
                        {{ deleting ? 'Eliminando...' : 'Eliminar' }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped src="../styles/debts-view.css"></style>
