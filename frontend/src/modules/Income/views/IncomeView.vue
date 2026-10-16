<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

import IncomeForm from '../components/IncomeForm.vue'
import RecurringIncomesModal from '../components/RecurringIncomesModal.vue'
import TransactionTable from '../../../components/common/TransactionTable.vue'
import { useIncomeStore } from '../stores/incomeStore'
import { useRecurringIncomeStore } from '../stores/recurringIncomeStore'
import { useAccountStore } from '../../Accounts/stores/accountStore'
import {
    confirmAction,
    extractErrorMessage,
    showError,
    showSuccess,
    withLoading,
} from '../../../utils/alerts'

const router = useRouter()

const incomeStore = useIncomeStore()

const recurringStore = useRecurringIncomeStore()

const accountStore = useAccountStore()

const showForm = ref(false)

const editingIncome = ref(null)

const editingSchedule = ref(null)

const showSchedules = ref(false)

const incomeToDelete = ref(null)

const deleting = ref(false)

const showNoAccountsModal = ref(false)

const activeAccounts = computed(() =>
    accountStore.accounts.filter((account) => account.is_active)
)

async function openCreate() {
    // Tras recargar, las cuentas pueden no haber llegado aún: se espera antes de decidir.
    try {
        await accountStore.ensureLoaded()
    } catch {
        showError('No fue posible cargar las cuentas.')

        return
    }

    if (activeAccounts.value.length === 0) {
        showNoAccountsModal.value = true

        return
    }

    editingIncome.value = null
    editingSchedule.value = null
    showForm.value = true
}

function openEdit(income) {
    editingIncome.value = income
    editingSchedule.value = null
    showForm.value = true
}

function openEditSchedule(schedule) {
    showSchedules.value = false
    editingIncome.value = null
    editingSchedule.value = schedule
    showForm.value = true
}

function goToAccounts() {
    showNoAccountsModal.value = false

    router.push({ name: 'cuentas' })
}

async function handleSaveIncome(data) {
    const isRecurring = data.is_recurring
    const isEditing = isRecurring
        ? editingSchedule.value !== null
        : editingIncome.value !== null

    try {
        await withLoading(
            isEditing ? 'Guardando cambios...' : 'Registrando ingreso...',
            () => saveIncome(data, isRecurring, isEditing)
        )

        handleCancel()

        showSuccess(
            isRecurring
                ? 'Ingreso recurrente guardado correctamente.'
                : isEditing
                  ? 'Ingreso actualizado correctamente.'
                  : 'Ingreso registrado correctamente.'
        )
    } catch (error) {
        showError(
            extractErrorMessage(
                error,
                'No fue posible guardar el ingreso.'
            )
        )
    }
}

async function saveIncome(data, isRecurring, isEditing) {
    const { is_recurring: _flag, ...payload } = data

    if (!isRecurring) {
        await (isEditing
            ? incomeStore.updateIncome(editingIncome.value.id, payload)
            : incomeStore.createIncome(payload))

        return
    }

    await (isEditing
        ? recurringStore.updateSchedule(editingSchedule.value.id, payload)
        : recurringStore.createSchedule(payload))

    // Al guardar puede registrarse de inmediato el ingreso de meses ya vencidos.
    await Promise.all([incomeStore.fetchIncomes(1), accountStore.fetchAccounts()])
}

async function toggleSchedule(schedule) {
    try {
        await withLoading('Actualizando...', async () => {
            await recurringStore.setActive(schedule.id, !schedule.is_active)
            await Promise.all([incomeStore.fetchIncomes(1), accountStore.fetchAccounts()])
        })
    } catch (error) {
        showError(
            extractErrorMessage(error, 'No fue posible actualizar el ingreso recurrente.')
        )
    }
}

async function deleteSchedule(schedule) {
    const confirmed = await confirmAction({
        title: 'Eliminar ingreso recurrente',
        text: `Se dejará de registrar «${schedule.description}» cada mes. Los ingresos ya registrados se conservan.`,
        confirmText: 'Eliminar',
        danger: true,
    })

    if (!confirmed) {
        return
    }

    try {
        await withLoading('Eliminando...', async () => {
            await recurringStore.deleteSchedule(schedule.id)
            await incomeStore.fetchIncomes()
        })

        showSuccess('Ingreso recurrente eliminado correctamente.')
    } catch (error) {
        showError(
            extractErrorMessage(error, 'No fue posible eliminar el ingreso recurrente.')
        )
    }
}

function goToPage(page) {
    incomeStore.fetchIncomes(page)
}

function handleCancel() {
    showForm.value = false
    editingIncome.value = null
    editingSchedule.value = null
}

function askDelete(income) {
    incomeToDelete.value = income
}

async function confirmDelete() {
    deleting.value = true

    try {
        await withLoading('Eliminando ingreso...', () =>
            incomeStore.deleteIncome(incomeToDelete.value.id)
        )

        incomeToDelete.value = null

        showSuccess('Ingreso eliminado correctamente.')
    } catch (error) {
        incomeToDelete.value = null

        showError(
            extractErrorMessage(
                error,
                'No fue posible eliminar el ingreso.'
            )
        )
    } finally {
        deleting.value = false
    }
}

onMounted(() => {
    incomeStore.fetchIncomes()
    recurringStore.fetchSchedules()

    accountStore.fetchAccounts().catch(() => {
        showError('No fue posible cargar las cuentas.')
    })
})
</script>

<template>
    <div class="income-page">

        <!-- HEADER -->

        <header class="page-header">

            <div>
                <h1 class="page-title">
                    Ingresos
                </h1>

                <p class="page-description">
                    Registra el dinero que recibes y a qué cuenta entra.
                </p>
            </div>

            <div
                v-if="!showForm"
                class="header-actions"
            >
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="showSchedules = true"
                >
                    Recurrentes ({{ recurringStore.schedules.length }})
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    @click="openCreate"
                >
                    + Nuevo ingreso
                </button>
            </div>

        </header>


        <!-- FORM -->

        <section
            v-if="showForm"
            class="form-card"
        >

            <div class="form-card-header">

                <div>
                    <h2>
                        {{
                            editingSchedule
                                ? 'Editar ingreso recurrente'
                                : editingIncome
                                  ? 'Editar ingreso'
                                  : 'Nuevo ingreso'
                        }}
                    </h2>

                    <p>
                        {{
                            editingSchedule || editingIncome
                                ? 'Modifica los datos del ingreso.'
                                : 'Registra un nuevo ingreso.'
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

                <IncomeForm
                    :key="editingSchedule ? `s-${editingSchedule.id}` : (editingIncome?.id ?? 'new')"
                    :income="editingIncome"
                    :schedule="editingSchedule"
                    :accounts="activeAccounts"
                    @submit="handleSaveIncome"
                    @cancel="handleCancel"
                />

            </div>

        </section>


        <!-- LOADING -->

        <div
            v-if="incomeStore.loading && incomeStore.incomes.length === 0 && !showForm"
            class="state-container"
        >
            <div class="loading-spinner"></div>

            <p>
                Cargando ingresos...
            </p>
        </div>


        <!-- ERROR -->

        <div
            v-else-if="incomeStore.error && !showForm"
            class="alert alert-error"
        >
            {{ incomeStore.error }}
        </div>


        <!-- EMPTY -->

        <section
            v-else-if="
                !incomeStore.loading &&
                incomeStore.incomes.length === 0 &&
                !showForm
            "
            class="empty-state"
        >

            <div class="empty-icon">
                $
            </div>

            <h2>
                No tienes ingresos registrados
            </h2>

            <p>
                Registra tus ingresos para llevar el control
                del dinero que entra a tus cuentas.
            </p>

            <button
                type="button"
                class="btn btn-primary"
                @click="openCreate"
            >
                Registrar mi primer ingreso
            </button>

        </section>


        <!-- INCOMES TABLE -->

        <TransactionTable
            v-else-if="incomeStore.incomes.length > 0 && !showForm"
            :items="incomeStore.incomes"
            :meta="incomeStore.meta"
            :loading="incomeStore.loading"
            date-key="received_at"
            variant="income"
            item-label="ingresos"
            @edit="openEdit"
            @delete="askDelete"
            @page-change="goToPage"
        />

        <!-- RECURRING INCOMES -->

        <RecurringIncomesModal
            v-if="showSchedules"
            :schedules="recurringStore.schedules"
            :loading="recurringStore.loading"
            @close="showSchedules = false"
            @edit="openEditSchedule"
            @toggle="toggleSchedule"
            @delete="deleteSchedule"
        />

        <!-- NO ACCOUNTS MODAL -->

        <div
            v-if="showNoAccountsModal"
            class="modal-overlay"
            @click.self="showNoAccountsModal = false"
        >
            <div class="modal" role="dialog" aria-modal="true">
                <h2>
                    Necesitas una cuenta
                </h2>

                <p>
                    Para registrar un ingreso primero debes crear una
                    cuenta activa en la sección de cuentas, donde
                    entrará el dinero.
                </p>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        @click="showNoAccountsModal = false"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="goToAccounts"
                    >
                        Ir a cuentas
                    </button>
                </div>
            </div>
        </div>


        <!-- DELETE CONFIRMATION -->

        <div
            v-if="incomeToDelete"
            class="modal-overlay"
            @click.self="!deleting && (incomeToDelete = null)"
        >
            <div class="modal" role="dialog" aria-modal="true">
                <h2>
                    Eliminar ingreso
                </h2>

                <p>
                    ¿Seguro que deseas eliminar el ingreso
                    <strong>{{ incomeToDelete.description }}</strong>?
                    El saldo de la cuenta se ajustará. Esta acción no
                    se puede deshacer.
                </p>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        :disabled="deleting"
                        @click="incomeToDelete = null"
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

<style scoped src="../styles/income-view.css"></style>
