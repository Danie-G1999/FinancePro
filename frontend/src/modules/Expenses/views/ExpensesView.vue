<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

import ExpenseForm from '../components/ExpenseForm.vue'
import TransactionTable from '../../../components/common/TransactionTable.vue'
import { useExpenseStore } from '../stores/expenseStore'
import { useAccountStore } from '../../Accounts/stores/accountStore'
import { useDebtStore } from '../../Debts/stores/debtStore'
import { useGoalStore } from '../../Goals/stores/goalStore'
import { useExpenseCategoryStore } from '../../ExpenseCategories/stores/expenseCategoryStore'
import {
    extractErrorMessage,
    showError,
    showSuccess,
    withLoading,
} from '../../../utils/alerts'

const router = useRouter()

const expenseStore = useExpenseStore()

const accountStore = useAccountStore()

const debtStore = useDebtStore()

const goalStore = useGoalStore()

const categoryStore = useExpenseCategoryStore()

const showForm = ref(false)

const editingExpense = ref(null)

const expenseToDelete = ref(null)

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

    editingExpense.value = null
    showForm.value = true
}

function openEdit(expense) {
    editingExpense.value = expense
    showForm.value = true
}

function goToAccounts() {
    showNoAccountsModal.value = false

    router.push({ name: 'cuentas' })
}

async function refreshBalances() {
    await Promise.all([
        accountStore.fetchAccounts(),
        debtStore.fetchDebts(),
        goalStore.fetchGoals(),
    ])
}

async function saveExpense(isEditing, data) {
    if (isEditing) {
        await expenseStore.updateExpense(editingExpense.value.id, data)
    } else {
        await expenseStore.createExpense(data)
    }

    // El saldo disponible de las cuentas cambia con cada gasto.
    await refreshBalances()
}

async function handleSaveExpense(data) {
    const isEditing = editingExpense.value !== null

    try {
        await withLoading(
            isEditing ? 'Guardando cambios...' : 'Registrando gasto...',
            () =>
                saveExpense(isEditing, data)
        )

        showForm.value = false
        editingExpense.value = null

        showSuccess(
            isEditing
                ? 'Gasto actualizado correctamente.'
                : 'Gasto registrado correctamente.'
        )
    } catch (error) {
        showError(
            extractErrorMessage(
                error,
                'No fue posible guardar el gasto.'
            )
        )
    }
}

function goToPage(page) {
    expenseStore.fetchExpenses(page)
}

function handleCancel() {
    showForm.value = false
    editingExpense.value = null
}

function askDelete(expense) {
    expenseToDelete.value = expense
}

async function confirmDelete() {
    deleting.value = true

    try {
        await withLoading('Eliminando gasto...', async () => {
            await expenseStore.deleteExpense(expenseToDelete.value.id)
            await refreshBalances()
        })

        expenseToDelete.value = null

        showSuccess('Gasto eliminado correctamente.')
    } catch (error) {
        expenseToDelete.value = null

        showError(
            extractErrorMessage(
                error,
                'No fue posible eliminar el gasto.'
            )
        )
    } finally {
        deleting.value = false
    }
}

onMounted(() => {
    expenseStore.fetchExpenses()
    debtStore.fetchDebts()
    goalStore.fetchGoals()
    categoryStore.fetchCategories()

    accountStore.fetchAccounts().catch(() => {
        showError('No fue posible cargar las cuentas.')
    })
})
</script>

<template>
    <div class="expense-page">

        <!-- HEADER -->

        <header class="page-header">

            <div>
                <h1 class="page-title">
                    Gastos
                </h1>

                <p class="page-description">
                    Registra el dinero que gastas y de qué cuenta sale.
                </p>
            </div>

            <button
                v-if="!showForm"
                type="button"
                class="btn btn-primary"
                @click="openCreate"
            >
                + Nuevo gasto
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
                        {{ editingExpense ? 'Editar gasto' : 'Nuevo gasto' }}
                    </h2>

                    <p>
                        {{
                            editingExpense
                                ? 'Modifica los datos del gasto.'
                                : 'Registra un nuevo gasto.'
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

                <ExpenseForm
                    :key="editingExpense?.id ?? 'new'"
                    :expense="editingExpense"
                    :accounts="activeAccounts"
                    :debts="debtStore.debts"
                    :goals="goalStore.goals"
                    :categories="categoryStore.categories"
                    @submit="handleSaveExpense"
                    @cancel="handleCancel"
                />

            </div>

        </section>


        <!-- LOADING -->

        <div
            v-if="expenseStore.loading && expenseStore.expenses.length === 0 && !showForm"
            class="state-container"
        >
            <div class="loading-spinner"></div>

            <p>
                Cargando gastos...
            </p>
        </div>


        <!-- ERROR -->

        <div
            v-else-if="expenseStore.error && !showForm"
            class="alert alert-error"
        >
            {{ expenseStore.error }}
        </div>


        <!-- EMPTY -->

        <section
            v-else-if="
                !expenseStore.loading &&
                expenseStore.expenses.length === 0 &&
                !showForm
            "
            class="empty-state"
        >

            <div class="empty-icon">
                $
            </div>

            <h2>
                No tienes gastos registrados
            </h2>

            <p>
                Registra tus gastos para llevar el control
                del dinero que sale de tus cuentas.
            </p>

            <button
                type="button"
                class="btn btn-primary"
                @click="openCreate"
            >
                Registrar mi primer gasto
            </button>

        </section>


        <!-- INCOMES TABLE -->

        <TransactionTable
            v-else-if="expenseStore.expenses.length > 0 && !showForm"
            :items="expenseStore.expenses"
            :meta="expenseStore.meta"
            :loading="expenseStore.loading"
            date-key="spent_at"
            variant="expense"
            item-label="gastos"
            @edit="openEdit"
            @delete="askDelete"
            @page-change="goToPage"
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
                    Para registrar un gasto primero debes crear una
                    cuenta activa en la sección de cuentas, donde
                    de la que saldrá el dinero.
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
            v-if="expenseToDelete"
            class="modal-overlay"
            @click.self="!deleting && (expenseToDelete = null)"
        >
            <div class="modal" role="dialog" aria-modal="true">
                <h2>
                    Eliminar gasto
                </h2>

                <p>
                    ¿Seguro que deseas eliminar el gasto
                    <strong>{{ expenseToDelete.description }}</strong>?
                    El valor se devolverá al saldo de la cuenta. Esta acción no
                    se puede deshacer.
                </p>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        :disabled="deleting"
                        @click="expenseToDelete = null"
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

<style scoped src="../styles/expense-view.css"></style>
