<script setup>
import { computed, reactive, ref, watch } from 'vue'

import {
    formatMoney,
    formatMoneyInput,
    moneyInputToNumber,
    numberToMoneyInput,
} from '../../../utils/money'
import { classifyExpense, suggestCategory } from '../utils/classifyExpense'
import { useBudgetTypes } from '../../ExpenseCategories/utils/budgetTypes'

const props = defineProps({
    expense: {
        type: Object,
        default: null,
    },

    accounts: {
        type: Array,
        required: true,
    },

    debts: {
        type: Array,
        default: () => [],
    },

    goals: {
        type: Array,
        default: () => [],
    },

    // Todas las categorías de gasto del usuario, activas e inactivas.
    categories: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['submit', 'cancel'])

const { budgetTypes, ruleLabel } = useBudgetTypes()

const loading = ref(false)
const errors = ref({})

function todayLocal() {
    const now = new Date()
    const month = String(now.getMonth() + 1).padStart(2, '0')
    const day = String(now.getDate()).padStart(2, '0')

    return `${now.getFullYear()}-${month}-${day}`
}

// Por defecto, la primera cuenta con la que realmente se puede pagar.
function defaultAccountId() {
    const payable = props.accounts.find(
        (account) =>
            account.type === 'credit_card' || Number(account.balance) > 0
    )

    return (payable ?? props.accounts[0])?.id ?? ''
}

const form = reactive({
    account_id: props.expense?.account_id ?? defaultAccountId(),
    description: props.expense?.description ?? '',
    amount: props.expense?.amount ?? '',
    spent_at: props.expense?.spent_at ?? todayLocal(),
    notes: props.expense?.notes ?? '',
    pays_debt: Boolean(props.expense?.debt_id),
    debt_id: props.expense?.debt_id ?? '',
    pays_goal: Boolean(props.expense?.goal_id),
    goal_id: props.expense?.goal_id ?? '',
    budget_type: props.expense?.budget_type ?? '',
    expense_category_id: props.expense?.expense_category_id ?? '',
    cost_type: props.expense?.cost_type ?? 'variable',
})

const display = reactive({
    amount: numberToMoneyInput(form.amount),
})

const selectedAccount = computed(() =>
    props.accounts.find((account) => account.id === Number(form.account_id))
)

const isCreditCard = computed(
    () => selectedAccount.value?.type === 'credit_card'
)

// Al editar, el gasto actual ya está descontado del saldo de su cuenta.
const availableBalance = computed(() => {
    if (!selectedAccount.value) {
        return 0
    }

    const restored =
        props.expense?.account_id === selectedAccount.value.id
            ? Number(props.expense.amount)
            : 0

    return Number(selectedAccount.value.balance) + restored
})

function handleAmountInput(event) {
    display.amount = formatMoneyInput(event.target.value)
    event.target.value = display.amount
    form.amount = moneyInputToNumber(display.amount)
}

const payableDebts = computed(() =>
    props.debts.filter(
        (debt) =>
            Number(debt.remaining_balance) > 0 ||
            debt.id === props.expense?.debt_id
    )
)

const selectedDebt = computed(() =>
    form.pays_debt
        ? props.debts.find((debt) => debt.id === Number(form.debt_id))
        : null
)

// Al editar, el pago actual ya está descontado del saldo de la deuda.
const debtRemaining = computed(() => {
    if (!selectedDebt.value) {
        return 0
    }

    const restored =
        props.expense?.debt_id === selectedDebt.value.id
            ? Number(props.expense.amount)
            : 0

    return Number(selectedDebt.value.remaining_balance) + restored
})

const currencyMismatch = computed(
    () =>
        selectedDebt.value &&
        selectedAccount.value &&
        selectedDebt.value.currency !== selectedAccount.value.currency
)

function setAmount(value) {
    form.amount = String(value)
    display.amount = numberToMoneyInput(form.amount)
}

const payableGoals = computed(() =>
    props.goals.filter(
        (goal) =>
            Number(goal.remaining_amount) > 0 ||
            goal.id === props.expense?.goal_id
    )
)

const selectedGoal = computed(() =>
    form.pays_goal
        ? props.goals.find((goal) => goal.id === Number(form.goal_id))
        : null
)

// Al editar, el aporte actual ya está sumado al ahorro de la meta.
const goalRemaining = computed(() => {
    if (!selectedGoal.value) {
        return 0
    }

    const restored =
        props.expense?.goal_id === selectedGoal.value.id
            ? Number(props.expense.amount)
            : 0

    return Number(selectedGoal.value.remaining_amount) + restored
})

const goalCurrencyMismatch = computed(
    () =>
        selectedGoal.value &&
        selectedAccount.value &&
        selectedGoal.value.currency !== selectedAccount.value.currency
)

// Pagos de deuda y aportes a metas se clasifican solos en el backend.
const isClassifiable = computed(() => !form.pays_debt && !form.pays_goal)

// Solo las del bloque elegido; una inactiva se conserva si el gasto ya la tenía.
const availableCategories = computed(() =>
    props.categories.filter(
        (category) =>
            category.budget_type === form.budget_type &&
            (category.is_active ||
                category.id === props.expense?.expense_category_id)
    )
)

// Al cambiar de bloque, la categoría anterior ya no aplica (salvo que la sugerencia
// haya puesto bloque y categoría a la vez).
watch(
    () => form.budget_type,
    (budgetType) => {
        const current = props.categories.find(
            (category) => category.id === Number(form.expense_category_id)
        )

        if (current?.budget_type !== budgetType) {
            form.expense_category_id = ''
        }
    }
)

// Valores sugeridos por la deuda; solo se limpian si el usuario no los modificó.
const debtAutofill = { description: null, amount: null }

function clearDebtAutofill() {
    if (debtAutofill.description !== null && form.description === debtAutofill.description) {
        form.description = ''
    }

    if (debtAutofill.amount !== null && form.amount === debtAutofill.amount) {
        form.amount = ''
        display.amount = ''
    }

    debtAutofill.description = null
    debtAutofill.amount = null
}

// Lo que el usuario elige a mano deja de sugerirse automáticamente.
const budgetTypeTouched = ref(Boolean(props.expense))
const categoryTouched = ref(Boolean(props.expense))
const autoClassified = ref(false)
const autoCategorized = ref(false)

// Primero intenta la categoría de gasto (que ya implica el bloque); si no hay
// coincidencia, cae al clasificador de bloque por palabras clave.
watch(
    () => form.description,
    (description) => {
        if (categoryTouched.value) {
            return
        }

        const candidates = props.categories.filter(
            (category) =>
                category.is_active &&
                (!budgetTypeTouched.value ||
                    category.budget_type === form.budget_type)
        )

        const category = suggestCategory(description, candidates)

        if (category) {
            if (!budgetTypeTouched.value) {
                form.budget_type = category.budget_type
                autoClassified.value = true
            }

            form.expense_category_id = category.id
            autoCategorized.value = true

            return
        }

        // La descripción ya no coincide: se retira la sugerencia anterior.
        if (autoCategorized.value) {
            form.expense_category_id = ''
            autoCategorized.value = false
        }

        if (budgetTypeTouched.value) {
            return
        }

        const suggested = classifyExpense(description)

        if (suggested) {
            form.budget_type = suggested
        }

        autoClassified.value = suggested !== null
    }
)

watch(
    () => form.pays_debt,
    (value) => {
        if (!value) {
            form.debt_id = ''
            clearDebtAutofill()
        } else {
            form.pays_goal = false
        }
    }
)

watch(
    () => form.pays_goal,
    (value) => {
        if (!value) {
            form.goal_id = ''
        } else {
            form.pays_debt = false
        }
    }
)

watch(selectedGoal, (goal) => {
    if (!goal || props.expense) {
        return
    }

    if (!form.description.trim()) {
        form.description = `Aporte a meta: ${goal.name}`
    }
})

watch(selectedDebt, (debt) => {
    if (!debt || props.expense) {
        return
    }

    if (!form.description.trim()) {
        form.description = `Pago de deuda: ${debt.name}`
        debtAutofill.description = form.description
    }

    if (!form.amount) {
        const suggested = Number(debt.monthly_payment) || debtRemaining.value

        setAmount(Math.min(suggested, debtRemaining.value))
        debtAutofill.amount = form.amount
    }
})

function validate() {
    errors.value = {}

    if (!form.account_id) {
        errors.value.account_id = 'Selecciona la cuenta de la que sale el dinero.'
    }

    if (!form.description.trim()) {
        errors.value.description = 'La descripción es obligatoria.'
    }

    if (!form.amount || Number(form.amount) <= 0) {
        errors.value.amount = 'El valor debe ser mayor a 0.'
    } else if (
        !isCreditCard.value &&
        Number(form.amount) > availableBalance.value
    ) {
        errors.value.amount =
            'El valor supera el saldo disponible de la cuenta.'
    } else if (
        selectedDebt.value &&
        Number(form.amount) > debtRemaining.value
    ) {
        errors.value.amount =
            'El valor supera el saldo pendiente de la deuda.'
    } else if (
        selectedGoal.value &&
        Number(form.amount) > goalRemaining.value
    ) {
        errors.value.amount =
            'El valor supera lo que falta para completar la meta.'
    }

    if (form.pays_goal && !form.goal_id) {
        errors.value.goal_id = 'Selecciona la meta a la que vas a aportar.'
    } else if (goalCurrencyMismatch.value) {
        errors.value.account_id =
            'La cuenta debe tener la misma moneda de la meta.'
    }

    if (form.pays_debt && !form.debt_id) {
        errors.value.debt_id = 'Selecciona la deuda que vas a pagar.'
    } else if (currencyMismatch.value) {
        errors.value.account_id =
            'La cuenta debe tener la misma moneda de la deuda.'
    }

    if (!form.spent_at) {
        errors.value.spent_at = 'La fecha es obligatoria.'
    }

    if (isClassifiable.value && !form.budget_type) {
        errors.value.budget_type = 'Selecciona la categoría del presupuesto.'
    }

    if (isClassifiable.value && form.budget_type && !form.expense_category_id) {
        errors.value.expense_category_id = 'Selecciona la categoría del gasto.'
    }

    return Object.keys(errors.value).length === 0
}

async function handleSubmit() {
    if (!validate()) {
        return
    }

    loading.value = true

    try {
        await emit('submit', {
            account_id: Number(form.account_id),
            description: form.description.trim(),
            amount: Number(form.amount),
            spent_at: form.spent_at,
            debt_id: form.pays_debt ? Number(form.debt_id) : null,
            goal_id: form.pays_goal ? Number(form.goal_id) : null,
            budget_type: isClassifiable.value ? form.budget_type : null,
            cost_type: isClassifiable.value ? form.cost_type : null,
            expense_category_id: isClassifiable.value
                ? Number(form.expense_category_id)
                : null,
            notes: form.notes.trim() || null,
        })
    } finally {
        loading.value = false
    }
}

function handleCancel() {
    if (loading.value) {
        return
    }

    emit('cancel')
}
</script>

<template>
    <form
        class="expense-form"
        @submit.prevent="handleSubmit"
    >
        <div class="form-group">
            <label for="expense-date" class="form-label">
                Fecha
                <span class="required">*</span>
            </label>

            <input
                id="expense-date"
                v-model="form.spent_at"
                type="date"
                class="form-input"
                :class="{ 'input-error': errors.spent_at }"
                :disabled="loading"
            />

            <span v-if="errors.spent_at" class="form-error">
                {{ errors.spent_at }}
            </span>
        </div>

        <div class="form-group">
            <span class="form-label">
                ¿Es el pago de una deuda?
            </span>

            <div class="segmented" role="group">
                <button
                    type="button"
                    class="segmented-option"
                    :class="{ active: !form.pays_debt }"
                    :disabled="loading"
                    @click="form.pays_debt = false"
                >
                    No
                </button>

                <button
                    type="button"
                    class="segmented-option"
                    :class="{ active: form.pays_debt }"
                    :disabled="loading || payableDebts.length === 0"
                    @click="form.pays_debt = true"
                >
                    Sí
                </button>
            </div>

            <span
                v-if="payableDebts.length === 0"
                class="form-help"
            >
                No tienes deudas con saldo pendiente.
            </span>
        </div>

        <div v-if="form.pays_debt" class="form-group">
            <label for="expense-debt" class="form-label">
                Deuda a pagar
                <span class="required">*</span>
            </label>

            <select
                id="expense-debt"
                v-model="form.debt_id"
                class="form-input"
                :class="{ 'input-error': errors.debt_id }"
                :disabled="loading"
            >
                <option value="" disabled>
                    Selecciona una deuda
                </option>

                <option
                    v-for="debt in payableDebts"
                    :key="debt.id"
                    :value="debt.id"
                >
                    {{ debt.name }} ({{ debt.currency }})
                </option>
            </select>

            <span v-if="errors.debt_id" class="form-error">
                {{ errors.debt_id }}
            </span>
        </div>

        <div
            v-if="selectedDebt"
            class="form-group debt-info-wrapper"
        >
            <div class="debt-info-card">
                <div class="debt-info-row">
                    <span>Valor total</span>
                    <strong>
                        {{ formatMoney(selectedDebt.total_amount, selectedDebt.currency) }}
                    </strong>
                </div>

                <div class="debt-info-row">
                    <span>Saldo pendiente</span>
                    <strong class="debt-info-pending">
                        {{ formatMoney(debtRemaining, selectedDebt.currency) }}
                    </strong>
                </div>

                <div class="debt-info-row">
                    <span>Cuotas pagadas</span>
                    <strong>
                        {{ selectedDebt.paid_installments }}
                        de {{ selectedDebt.installments }} pactadas
                    </strong>
                </div>

                <div
                    v-if="Number(selectedDebt.monthly_payment)"
                    class="debt-info-row"
                >
                    <span>Valor de la cuota</span>
                    <strong>
                        {{ formatMoney(selectedDebt.monthly_payment, selectedDebt.currency) }}
                    </strong>
                </div>
            </div>

            <span v-if="currencyMismatch" class="form-error">
                La cuenta seleccionada está en {{ selectedAccount.currency }}
                y la deuda en {{ selectedDebt.currency }}.
            </span>
        </div>

        <div class="form-group">
            <label for="expense-account" class="form-label">
                Cuenta de origen
                <span class="required">*</span>
            </label>

            <select
                id="expense-account"
                v-model="form.account_id"
                class="form-input"
                :class="{ 'input-error': errors.account_id }"
                :disabled="loading"
            >
                <option
                    v-for="account in accounts"
                    :key="account.id"
                    :value="account.id"
                >
                    {{ account.name }} ({{ account.currency }})
                </option>
            </select>

            <span v-if="errors.account_id" class="form-error">
                {{ errors.account_id }}
            </span>

            <div
                v-if="selectedAccount"
                class="balance-card"
                :class="{ 'balance-card-low': availableBalance <= 0 && !isCreditCard }"
            >
                <span class="balance-card-label">
                    Saldo disponible a la fecha
                </span>

                <strong class="balance-card-value">
                    {{ formatMoney(availableBalance, selectedAccount.currency) }}
                </strong>
            </div>

            <span class="form-help">
                El dinero se descontará del saldo de esta cuenta.
            </span>
        </div>

        <div class="form-group">
            <label for="expense-amount" class="form-label">
                Valor
                <span class="required">*</span>
            </label>

            <div class="input-money">
                <span class="currency-prefix">$</span>

                <input
                    id="expense-amount"
                    :value="display.amount"
                    type="text"
                    inputmode="decimal"
                    class="form-input money-input"
                    :class="{ 'input-error': errors.amount }"
                    placeholder="0"
                    :disabled="loading"
                    @input="handleAmountInput"
                />
            </div>

            <span v-if="errors.amount" class="form-error">
                {{ errors.amount }}
            </span>

            <span v-if="selectedAccount" class="form-help">
                Moneda: {{ selectedAccount.currency }}
            </span>
        </div>

        <div class="form-group">
            <label for="expense-description" class="form-label">
                Descripción
                <span class="required">*</span>
            </label>

            <input
                id="expense-description"
                v-model="form.description"
                type="text"
                class="form-input"
                :class="{ 'input-error': errors.description }"
                placeholder="Ej: Mercado de la semana"
                maxlength="150"
                :disabled="loading"
            />

            <span v-if="errors.description" class="form-error">
                {{ errors.description }}
            </span>
        </div>

        <div class="form-group">
            <span class="form-label">
                ¿Es un aporte a una meta de ahorro?
            </span>

            <div class="segmented" role="group">
                <button
                    type="button"
                    class="segmented-option"
                    :class="{ active: !form.pays_goal }"
                    :disabled="loading"
                    @click="form.pays_goal = false"
                >
                    No
                </button>

                <button
                    type="button"
                    class="segmented-option"
                    :class="{ active: form.pays_goal }"
                    :disabled="loading || payableGoals.length === 0"
                    @click="form.pays_goal = true"
                >
                    Sí
                </button>
            </div>

            <span
                v-if="payableGoals.length === 0"
                class="form-help"
            >
                No tienes metas con valor pendiente. Créalas en Presupuestos.
            </span>
        </div>

        <div v-if="form.pays_goal" class="form-group">
            <label for="expense-goal" class="form-label">
                Meta de ahorro
                <span class="required">*</span>
            </label>

            <select
                id="expense-goal"
                v-model="form.goal_id"
                class="form-input"
                :class="{ 'input-error': errors.goal_id }"
                :disabled="loading"
            >
                <option value="" disabled>
                    Selecciona una meta
                </option>

                <option
                    v-for="goal in payableGoals"
                    :key="goal.id"
                    :value="goal.id"
                >
                    {{ goal.name }} ({{ goal.currency }})
                </option>
            </select>

            <span v-if="errors.goal_id" class="form-error">
                {{ errors.goal_id }}
            </span>
        </div>

        <div
            v-if="selectedGoal"
            class="form-group debt-info-wrapper"
        >
            <div class="debt-info-card">
                <div class="debt-info-row">
                    <span>Valor objetivo</span>
                    <strong>
                        {{ formatMoney(selectedGoal.target_amount, selectedGoal.currency) }}
                    </strong>
                </div>

                <div class="debt-info-row">
                    <span>Ahorrado</span>
                    <strong>
                        {{ formatMoney(selectedGoal.saved_amount, selectedGoal.currency) }}
                    </strong>
                </div>

                <div class="debt-info-row">
                    <span>Falta para la meta</span>
                    <strong class="debt-info-pending">
                        {{ formatMoney(goalRemaining, selectedGoal.currency) }}
                    </strong>
                </div>

                <div class="debt-info-row">
                    <span>Progreso</span>
                    <strong>{{ selectedGoal.progress_percent }}%</strong>
                </div>
            </div>

            <span v-if="goalCurrencyMismatch" class="form-error">
                La cuenta seleccionada está en {{ selectedAccount.currency }}
                y la meta en {{ selectedGoal.currency }}.
            </span>
        </div>

        <template v-if="isClassifiable">
            <div class="form-group">
                <label for="expense-budget-type" class="form-label">
                    Categoría del presupuesto
                    <span class="required">*</span>

                    <span
                        class="info-tip"
                        tabindex="0"
                        role="note"
                        :aria-label="`Información sobre tu reparto ${ruleLabel}`"
                    >
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 11v5" />
                            <path d="M12 8h.01" />
                        </svg>

                        <span class="info-tip-content">
                            Aplicamos tu reparto {{ ruleLabel }} sobre tus ingresos:
                            <br>
                            {{ budgetTypes[0].percent }}% necesidades <br>{{ budgetTypes[1].percent }}% deseos
                            <br>{{ budgetTypes[2].percent }}% ahorro y metas. <br>Los pagos de deuda y
                            aportes a metas se clasifican automáticamente. Puedes cambiar el reparto en
                            Configuración.
                        </span>
                    </span>
                </label>

                <select
                    id="expense-budget-type"
                    v-model="form.budget_type"
                    class="form-input"
                    :class="{ 'input-error': errors.budget_type }"
                    :disabled="loading"
                    @change="budgetTypeTouched = true; autoClassified = false; autoCategorized = false"
                >
                    <option value="" disabled>
                        Selecciona una categoría
                    </option>

                    <option
                        v-for="type in budgetTypes"
                        :key="type.value"
                        :value="type.value"
                    >
                        {{ type.label }}
                    </option>
                </select>

                <span v-if="errors.budget_type" class="form-error">
                    {{ errors.budget_type }}
                </span>

                <span v-else-if="autoClassified" class="form-help">
                    Clasificado automáticamente según la descripción. Puedes cambiarlo.
                </span>
            </div>

            <div class="form-group">
                <label for="expense-category" class="form-label">
                    Categoría de gasto
                    <span class="required">*</span>
                </label>

                <!-- Elegirla a mano detiene las sugerencias según la descripción. -->
                <select
                    id="expense-category"
                    v-model="form.expense_category_id"
                    class="form-input"
                    :class="{ 'input-error': errors.expense_category_id }"
                    :disabled="loading || !form.budget_type || availableCategories.length === 0"
                    @change="budgetTypeTouched = true; categoryTouched = true; autoCategorized = false"
                >
                    <option value="" disabled>
                        {{
                            form.budget_type
                                ? 'Selecciona una categoría de gasto'
                                : 'Primero elige la categoría del presupuesto'
                        }}
                    </option>

                    <option
                        v-for="category in availableCategories"
                        :key="category.id"
                        :value="category.id"
                    >
                        {{ category.icon ? `${category.icon} ` : '' }}{{ category.name }}{{ category.is_active ? '' : ' (inactiva)' }}
                    </option>
                </select>

                <span v-if="errors.expense_category_id" class="form-error">
                    {{ errors.expense_category_id }}
                </span>

                <span v-else-if="autoCategorized" class="form-help">
                    Sugerida según la descripción. Puedes cambiarla.
                </span>

                <span
                    v-else-if="form.budget_type && availableCategories.length === 0"
                    class="form-help"
                >
                    No tienes categorías activas en este bloque.
                    <router-link to="/categorias-gasto">Crear una</router-link>
                </span>
            </div>

            <div class="form-group">
                <span class="form-label">
                    Tipo de gasto
                    <span class="required">*</span>
                </span>

                <div class="segmented" role="group">
                    <button
                        type="button"
                        class="segmented-option"
                        :class="{ active: form.cost_type === 'fixed' }"
                        :disabled="loading"
                        @click="form.cost_type = 'fixed'"
                    >
                        Fijo
                    </button>

                    <button
                        type="button"
                        class="segmented-option"
                        :class="{ active: form.cost_type === 'variable' }"
                        :disabled="loading"
                        @click="form.cost_type = 'variable'"
                    >
                        Variable
                    </button>
                </div>
            </div>
        </template>

        <div class="form-group form-group-full">
            <label for="expense-notes" class="form-label">
                Notas
            </label>

            <textarea
                id="expense-notes"
                v-model="form.notes"
                class="form-input form-textarea"
                placeholder="Agrega una nota opcional..."
                maxlength="500"
                rows="2"
                :disabled="loading"
            ></textarea>

            <div class="character-count">
                {{ form.notes.length }}/500
            </div>
        </div>

        <div class="form-actions">
            <button
                type="button"
                class="btn btn-secondary"
                :disabled="loading"
                @click="handleCancel"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="btn btn-primary"
                :disabled="loading"
            >
                <span v-if="loading">Guardando...</span>

                <span v-else>
                    {{ expense ? 'Guardar cambios' : 'Registrar gasto' }}
                </span>
            </button>
        </div>
    </form>
</template>

<style scoped src="../styles/expense-form.css"></style>
