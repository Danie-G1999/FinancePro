<script setup>
import { computed, reactive, ref } from 'vue'

import {
    formatMoneyInput,
    moneyInputToNumber,
    numberToMoneyInput,
} from '../../../utils/money'

const props = defineProps({
    income: {
        type: Object,
        default: null,
    },

    // Ingreso recurrente que se está editando.
    schedule: {
        type: Object,
        default: null,
    },

    accounts: {
        type: Array,
        required: true,
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

const source = props.income ?? props.schedule

const form = reactive({
    account_id: source?.account_id ?? props.accounts[0]?.id ?? '',
    description: source?.description ?? '',
    amount: source?.amount ?? '',
    received_at:
        props.income?.received_at ??
        props.schedule?.start_date ??
        todayLocal(),
    notes: source?.notes ?? '',
    is_recurring: Boolean(props.schedule),
    recurring_type: props.schedule?.type ?? 'salary',
    day_of_month: props.schedule?.day_of_month ?? new Date().getDate(),
})

// El tipo de ingreso no se puede cambiar al editar.
const isEditing = Boolean(props.income || props.schedule)

const recurringHint = computed(() => {
    const account = selectedAccount.value?.name
    const day = Number(form.day_of_month)

    if (!account || !day) {
        return ''
    }

    const shortMonths = day > 28
        ? ' En los meses más cortos se registrará el último día del mes.'
        : ''

    return `Se registrará automáticamente el día ${day} de cada mes en ${account}.${shortMonths}`
})

const display = reactive({
    amount: numberToMoneyInput(form.amount),
})

const selectedAccount = computed(() =>
    props.accounts.find((account) => account.id === Number(form.account_id))
)

function handleAmountInput(event) {
    display.amount = formatMoneyInput(event.target.value)
    event.target.value = display.amount
    form.amount = moneyInputToNumber(display.amount)
}

function validate() {
    errors.value = {}

    if (!form.account_id) {
        errors.value.account_id = 'Selecciona la cuenta que recibe el dinero.'
    }

    if (!form.description.trim()) {
        errors.value.description = 'La descripción es obligatoria.'
    }

    if (!form.amount || Number(form.amount) <= 0) {
        errors.value.amount = 'El valor debe ser mayor a 0.'
    }

    if (!form.received_at) {
        errors.value.received_at = 'La fecha es obligatoria.'
    }

    const day = Number(form.day_of_month)

    if (form.is_recurring && (!Number.isInteger(day) || day < 1 || day > 31)) {
        errors.value.day_of_month = 'Indica un día entre 1 y 31.'
    }

    return Object.keys(errors.value).length === 0
}

async function handleSubmit() {
    if (!validate()) {
        return
    }

    loading.value = true

    try {
        const base = {
            account_id: Number(form.account_id),
            description: form.description.trim(),
            amount: Number(form.amount),
            notes: form.notes.trim() || null,
        }

        await emit(
            'submit',
            form.is_recurring
                ? {
                      ...base,
                      is_recurring: true,
                      type: form.recurring_type,
                      day_of_month: Number(form.day_of_month),
                      start_date: form.received_at,
                  }
                : { ...base, is_recurring: false, received_at: form.received_at }
        )
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
        class="income-form"
        @submit.prevent="handleSubmit"
    >
        <div class="form-group">
            <label for="income-description" class="form-label">
                Descripción
                <span class="required">*</span>
            </label>

            <input
                id="income-description"
                v-model="form.description"
                type="text"
                class="form-input"
                :class="{ 'input-error': errors.description }"
                placeholder="Ej: Salario de octubre"
                maxlength="150"
                :disabled="loading"
            />

            <span v-if="errors.description" class="form-error">
                {{ errors.description }}
            </span>
        </div>

        <div class="form-group">
            <label for="income-account" class="form-label">
                Cuenta de destino
                <span class="required">*</span>
            </label>

            <select
                id="income-account"
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

            <span class="form-help">
                El dinero se sumará al saldo de esta cuenta.
            </span>
        </div>

        <div class="form-group">
            <label for="income-amount" class="form-label">
                Valor
                <span class="required">*</span>
            </label>

            <div class="input-money">
                <span class="currency-prefix">$</span>

                <input
                    id="income-amount"
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
            <label for="income-date" class="form-label">
                {{ form.is_recurring ? 'Fecha de inicio' : 'Fecha' }}
                <span class="required">*</span>
            </label>

            <input
                id="income-date"
                v-model="form.received_at"
                type="date"
                class="form-input"
                :class="{ 'input-error': errors.received_at }"
                :disabled="loading"
            />

            <span v-if="errors.received_at" class="form-error">
                {{ errors.received_at }}
            </span>
        </div>

        <div class="form-group">
            <span class="form-label">
                ¿Es un ingreso recurrente?
            </span>

            <div class="segmented" role="group">
                <button
                    type="button"
                    class="segmented-option"
                    :class="{ active: !form.is_recurring }"
                    :disabled="loading || isEditing"
                    @click="form.is_recurring = false"
                >
                    No
                </button>

                <button
                    type="button"
                    class="segmented-option"
                    :class="{ active: form.is_recurring }"
                    :disabled="loading || isEditing"
                    @click="form.is_recurring = true"
                >
                    Sí
                </button>
            </div>

            <span class="form-help">
                Útil para salarios o ingresos que llegan cada mes.
            </span>
        </div>

        <div v-if="form.is_recurring" class="form-group">
            <label for="income-recurring-type" class="form-label">
                Tipo de ingreso
                <span class="required">*</span>
            </label>

            <select
                id="income-recurring-type"
                v-model="form.recurring_type"
                class="form-input"
                :disabled="loading"
            >
                <option value="salary">Salario</option>
                <option value="other">Otro ingreso recurrente</option>
            </select>
        </div>

        <div v-if="form.is_recurring" class="form-group">
            <label for="income-day" class="form-label">
                Día del mes en que llega
                <span class="required">*</span>
            </label>

            <input
                id="income-day"
                v-model="form.day_of_month"
                type="number"
                class="form-input"
                :class="{ 'input-error': errors.day_of_month }"
                min="1"
                max="31"
                step="1"
                placeholder="Ej: 30"
                :disabled="loading"
            />

            <span v-if="errors.day_of_month" class="form-error">
                {{ errors.day_of_month }}
            </span>
        </div>

        <p
            v-if="form.is_recurring && recurringHint"
            class="recurring-hint"
        >
            {{ recurringHint }}
        </p>

        <div class="form-group form-group-full">
            <label for="income-notes" class="form-label">
                Notas
            </label>

            <textarea
                id="income-notes"
                v-model="form.notes"
                class="form-input form-textarea"
                placeholder="Agrega una nota opcional..."
                maxlength="500"
                rows="3"
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
                    {{ isEditing ? 'Guardar cambios' : form.is_recurring ? 'Guardar ingreso recurrente' : 'Registrar ingreso' }}
                </span>
            </button>
        </div>
    </form>
</template>

<style scoped src="../styles/income-form.css"></style>
