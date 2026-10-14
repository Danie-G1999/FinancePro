<script setup>
import { computed, reactive, ref, watch } from 'vue'

import {
    formatMoney,
    formatMoneyInput,
    moneyInputToNumber,
    numberToMoneyInput,
} from '../../../utils/money'

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
})

const emit = defineEmits(['submit', 'cancel'])

const loading = ref(false)
const errors = ref({})

function todayLocal() {
    const now = new Date()
    const month = String(now.getMonth() + 1).padStart(2, '0')
    const day = String(now.getDate()).padStart(2, '0')

    return `${now.getFullYear()}-${month}-${day}`
}

const form = reactive({
    account_id: props.expense?.account_id ?? props.accounts[0]?.id ?? '',
    description: props.expense?.description ?? '',
    amount: props.expense?.amount ?? '',
    spent_at: props.expense?.spent_at ?? todayLocal(),
    notes: props.expense?.notes ?? '',
    pays_debt: Boolean(props.expense?.debt_id),
    debt_id: props.expense?.debt_id ?? '',
    pays_goal: Boolean(props.expense?.goal_id),
    goal_id: props.expense?.goal_id ?? '',
    budget_type: ['need', 'want'].includes(props.expense?.budget_type)
        ? props.expense.budget_type
        : 'need',
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

watch(
    () => form.pays_debt,
    (value) => {
        if (!value) {
            form.debt_id = ''
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
    }

    if (!form.amount) {
        const suggested = Number(debt.monthly_payment) || debtRemaining.value

        setAmount(Math.min(suggested, debtRemaining.value))
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
                </label>

                <select
                    id="expense-budget-type"
                    v-model="form.budget_type"
                    class="form-input"
                    :disabled="loading"
                >
                    <option value="need">Necesidad (50%)</option>
                    <option value="want">Deseo (30%)</option>
                </select>
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
