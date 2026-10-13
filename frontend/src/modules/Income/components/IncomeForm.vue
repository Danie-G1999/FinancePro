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

const form = reactive({
    account_id: props.income?.account_id ?? props.accounts[0]?.id ?? '',
    description: props.income?.description ?? '',
    amount: props.income?.amount ?? '',
    received_at: props.income?.received_at ?? todayLocal(),
    notes: props.income?.notes ?? '',
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
            received_at: form.received_at,
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
                Fecha
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
                    {{ income ? 'Guardar cambios' : 'Registrar ingreso' }}
                </span>
            </button>
        </div>
    </form>
</template>

<style scoped src="../styles/income-form.css"></style>
