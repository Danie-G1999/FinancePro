<script setup>
import { computed, onMounted, ref } from 'vue'

import BaseModal from '../../../components/common/BaseModal.vue'
import BudgetFormula from '../components/BudgetFormula.vue'
import BudgetIncomeModal from '../components/BudgetIncomeModal.vue'
import BudgetRuleCard from '../components/BudgetRuleCard.vue'
import GoalCard from '../../Goals/components/GoalCard.vue'
import GoalFormModal from '../../Goals/components/GoalFormModal.vue'
import { useBudgetStore } from '../stores/budgetStore'
import { useGoalStore } from '../../Goals/stores/goalStore'
import { formatMoney } from '../../../utils/money'
import {
    extractErrorMessage,
    showError,
    showSuccess,
    withLoading,
} from '../../../utils/alerts'

const RULE_DESCRIPTIONS = {
    need: 'Vivienda, servicios, mercado, transporte y salud.',
    want: 'Entretenimiento, salidas, compras personales y suscripciones.',
    savings: 'Aportes a metas de ahorro y pago de deudas.',
}

const budgetStore = useBudgetStore()

const goalStore = useGoalStore()

const showIncomeModal = ref(false)

const showGoalModal = ref(false)

const editingGoal = ref(null)

const goalToDelete = ref(null)

const deleting = ref(false)

const overview = computed(() => budgetStore.overview)

const monthLabel = computed(() => {
    const [year, month] = budgetStore.period.split('-').map(Number)

    const label = new Intl.DateTimeFormat('es-CO', {
        month: 'long',
        year: 'numeric',
    }).format(new Date(year, month - 1, 1))

    return label.charAt(0).toUpperCase() + label.slice(1)
})

const incomeNote = computed(() => {
    if (!overview.value) {
        return ''
    }

    const { income, currency } = overview.value

    return income.source === 'planned'
        ? `Ingreso neto definido por ti. Registrado este mes: ${formatMoney(income.registered, currency)}.`
        : 'Calculado con los ingresos que registraste este mes.'
})

function shiftMonth(delta) {
    const [year, month] = budgetStore.period.split('-').map(Number)

    const date = new Date(year, month - 1 + delta, 1)

    const period = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`

    budgetStore.fetchOverview(period)
}

function handlePeriodInput(event) {
    if (event.target.value) {
        budgetStore.fetchOverview(event.target.value)
    }
}

async function handleSaveIncome(data) {
    try {
        await withLoading('Guardando presupuesto...', () =>
            budgetStore.saveBudget(data)
        )

        showIncomeModal.value = false

        showSuccess('Presupuesto guardado correctamente.')
    } catch (error) {
        showError(
            extractErrorMessage(error, 'No fue posible guardar el presupuesto.')
        )
    }
}

async function handleResetIncome() {
    try {
        await withLoading('Actualizando presupuesto...', () =>
            budgetStore.resetBudget()
        )

        showIncomeModal.value = false

        showSuccess('Se usarán tus ingresos registrados.')
    } catch (error) {
        showError(
            extractErrorMessage(error, 'No fue posible actualizar el presupuesto.')
        )
    }
}

function openCreateGoal() {
    editingGoal.value = null
    showGoalModal.value = true
}

function openEditGoal(goal) {
    editingGoal.value = goal
    showGoalModal.value = true
}

function closeGoalModal() {
    showGoalModal.value = false
    editingGoal.value = null
}

async function handleSaveGoal(data) {
    const isEditing = editingGoal.value !== null

    try {
        await withLoading(
            isEditing ? 'Guardando cambios...' : 'Creando meta...',
            () =>
                isEditing
                    ? goalStore.updateGoal(editingGoal.value.id, data)
                    : goalStore.createGoal(data)
        )

        closeGoalModal()

        showSuccess(
            isEditing
                ? 'Meta actualizada correctamente.'
                : 'Meta creada correctamente.'
        )
    } catch (error) {
        showError(
            extractErrorMessage(error, 'No fue posible guardar la meta.')
        )
    }
}

async function confirmDeleteGoal() {
    deleting.value = true

    try {
        await withLoading('Eliminando meta...', async () => {
            await goalStore.deleteGoal(goalToDelete.value.id)

            // Los aportes de la meta quedan como gastos normales.
            await budgetStore.fetchOverview()
        })

        goalToDelete.value = null

        showSuccess('Meta eliminada correctamente.')
    } catch (error) {
        goalToDelete.value = null

        showError(
            extractErrorMessage(error, 'No fue posible eliminar la meta.')
        )
    } finally {
        deleting.value = false
    }
}

onMounted(() => {
    budgetStore.fetchOverview()
    goalStore.fetchGoals()
})
</script>

<template>
    <div class="budgets-page">

        <!-- HEADER -->

        <header class="page-header">

            <div>
                <h1 class="page-title">
                    Presupuestos
                </h1>

                <p class="page-description">
                    Organiza tus ingresos con la regla 50/30/20 y sigue tus metas de ahorro.
                </p>
            </div>

            <div class="period-nav">
                <button
                    type="button"
                    class="icon-nav"
                    aria-label="Mes anterior"
                    @click="shiftMonth(-1)"
                >
                    ‹
                </button>

                <label class="period-label">
                    <span>{{ monthLabel }}</span>

                    <input
                        type="month"
                        :value="budgetStore.period"
                        aria-label="Seleccionar mes"
                        @change="handlePeriodInput"
                    />
                </label>

                <button
                    type="button"
                    class="icon-nav"
                    aria-label="Mes siguiente"
                    @click="shiftMonth(1)"
                >
                    ›
                </button>
            </div>

        </header>


        <!-- LOADING / ERROR -->

        <div
            v-if="budgetStore.loading && !overview"
            class="state-container"
        >
            <div class="loading-spinner"></div>

            <p>Cargando presupuesto...</p>
        </div>

        <div
            v-else-if="budgetStore.error && !overview"
            class="alert alert-error"
        >
            {{ budgetStore.error }}
        </div>


        <template v-else-if="overview">

            <!-- INCOME -->

            <section class="income-bar">

                <div>
                    <span>Ingreso neto de {{ monthLabel }}</span>

                    <strong>
                        {{ formatMoney(overview.income.amount, overview.currency) }}
                    </strong>

                    <small>{{ incomeNote }}</small>
                </div>

                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="showIncomeModal = true"
                >
                    Definir ingreso neto
                </button>

            </section>

            <p
                v-if="overview.income.amount <= 0"
                class="empty-hint"
            >
                Aún no hay ingresos para este mes. Registra un ingreso o
                define tu ingreso neto para calcular tu presupuesto.
            </p>


            <!-- FORMULA -->

            <BudgetFormula
                :formula="overview.formula"
                :currency="overview.currency"
            />


            <!-- RULE 50/30/20 -->

            <section class="rule-grid">

                <BudgetRuleCard
                    v-for="(block, type) in overview.rule"
                    :key="type"
                    :block="block"
                    :description="RULE_DESCRIPTIONS[type]"
                    :currency="overview.currency"
                />

            </section>

        </template>


        <!-- GOALS -->

        <section class="goals-section">

            <div class="section-header">

                <div>
                    <h2>Metas de ahorro</h2>

                    <p>
                        Envía dinero a una meta registrando un gasto
                        como aporte a ahorro.
                    </p>
                </div>

                <button
                    type="button"
                    class="btn btn-primary"
                    @click="openCreateGoal"
                >
                    + Nueva meta
                </button>

            </div>

            <div
                v-if="goalStore.loading && goalStore.goals.length === 0"
                class="state-container"
            >
                <div class="loading-spinner"></div>
            </div>

            <div
                v-else-if="goalStore.error && goalStore.goals.length === 0"
                class="alert alert-error"
            >
                {{ goalStore.error }}
            </div>

            <div
                v-else-if="goalStore.goals.length === 0"
                class="empty-state"
            >
                <div class="empty-icon">
                    ★
                </div>

                <h3>Aún no tienes metas</h3>

                <p>
                    Crea una meta, por ejemplo un fondo de emergencia,
                    y ahorra hacia ella desde el módulo de gastos.
                </p>
            </div>

            <div
                v-else
                class="goals-grid"
            >
                <GoalCard
                    v-for="goal in goalStore.goals"
                    :key="goal.id"
                    :goal="goal"
                    @edit="openEditGoal"
                    @delete="goalToDelete = $event"
                />
            </div>

        </section>


        <!-- MODALS -->

        <BudgetIncomeModal
            v-if="showIncomeModal && overview"
            :overview="overview"
            :month-label="monthLabel"
            @submit="handleSaveIncome"
            @reset="handleResetIncome"
            @cancel="showIncomeModal = false"
        />

        <GoalFormModal
            v-if="showGoalModal"
            :goal="editingGoal"
            @submit="handleSaveGoal"
            @cancel="closeGoalModal"
        />

        <BaseModal
            v-if="goalToDelete"
            title="Eliminar meta"
            max-width="420px"
            :persistent="deleting"
            @close="goalToDelete = null"
        >
            <p class="confirm-text">
                ¿Seguro que deseas eliminar la meta
                <strong>{{ goalToDelete.name }}</strong>?
                Los aportes ya registrados se conservarán como gastos
                y su saldo no volverá a tus cuentas. Esta acción no se
                puede deshacer.
            </p>

            <div class="modal-actions">
                <button
                    type="button"
                    class="btn btn-secondary"
                    :disabled="deleting"
                    @click="goalToDelete = null"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    class="btn btn-danger"
                    :disabled="deleting"
                    @click="confirmDeleteGoal"
                >
                    {{ deleting ? 'Eliminando...' : 'Eliminar' }}
                </button>
            </div>
        </BaseModal>

    </div>
</template>

<style scoped src="../styles/budgets-view.css"></style>
