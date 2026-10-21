<script setup>
import { reactive, ref } from 'vue'
import { CURRENCIES } from '../../../utils/currencies'
import { useCurrencyFormatter } from '../../../composables/useCurrencyFormatter'

import {
    formatMoneyInput,
    moneyInputToNumber,
    numberToMoneyInput,
} from '../../../utils/money'

// Las nuevas cuentas, deudas y metas usan la moneda preferida del usuario.
const { preferredCurrency } = useCurrencyFormatter()

const props = defineProps({
    debt: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['submit', 'cancel'])

const loading = ref(false)
const errors = ref({})

const form = reactive({
    name: props.debt?.name ?? '',
    type: props.debt?.type ?? 'personal_loan',
    creditor: props.debt?.creditor ?? '',
    total_amount: props.debt?.total_amount ?? '',
    installments: props.debt?.installments ?? 1,
    payment_frequency: props.debt?.payment_frequency ?? 'monthly',
    payment_day: props.debt?.payment_day ?? '',
    second_payment_day: props.debt?.second_payment_day ?? '',
    interest_rate: props.debt?.interest_rate ?? '',
    monthly_payment: props.debt?.monthly_payment ?? '',
    currency: props.debt?.currency ?? preferredCurrency.value,
    due_date: props.debt?.due_date ?? '',
    description: props.debt?.description ?? '',
})

const display = reactive({
    total_amount: numberToMoneyInput(form.total_amount),
    monthly_payment: numberToMoneyInput(form.monthly_payment),
})

const debtTypes = [
    { value: 'credit_card', label: 'Tarjeta de crédito' },
    { value: 'personal_loan', label: 'Préstamo personal' },
    { value: 'mortgage', label: 'Hipoteca' },
    { value: 'vehicle', label: 'Vehículo' },
    { value: 'student', label: 'Crédito educativo' },
    { value: 'informal', label: 'Préstamo informal' },
    { value: 'other', label: 'Otro' },
]

function handleMoneyInput(field, event) {
    display[field] = formatMoneyInput(event.target.value)
    event.target.value = display[field]
    form[field] = moneyInputToNumber(display[field])
}

function isValidDay(value) {
    const day = Number(value)

    return value !== '' && Number.isInteger(day) && day >= 1 && day <= 31
}

function validate() {
    errors.value = {}

    if (!form.name.trim()) {
        errors.value.name = 'El nombre de la deuda es obligatorio.'
    }

    if (!form.total_amount || Number(form.total_amount) <= 0) {
        errors.value.total_amount =
            'El valor total debe ser mayor a 0.'
    }

    if (
        !Number.isInteger(Number(form.installments)) ||
        Number(form.installments) < 1
    ) {
        errors.value.installments =
            'Ingresa un número de cuotas válido (mínimo 1).'
    }

    if (!isValidDay(form.payment_day)) {
        errors.value.payment_day =
            'Indica un día de pago entre 1 y 31.'
    }

    if (form.payment_frequency === 'biweekly') {
        if (!isValidDay(form.second_payment_day)) {
            errors.value.second_payment_day =
                'Indica el segundo día de pago entre 1 y 31.'
        } else if (
            Number(form.second_payment_day) === Number(form.payment_day)
        ) {
            errors.value.second_payment_day =
                'Los dos días de pago deben ser distintos.'
        }
    }

    if (
        form.interest_rate !== '' &&
        (Number(form.interest_rate) < 0 ||
            Number(form.interest_rate) > 999.99)
    ) {
        errors.value.interest_rate =
            'La tasa de interés no es válida.'
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
            name: form.name.trim(),
            type: form.type,
            creditor: form.creditor.trim() || null,
            total_amount: Number(form.total_amount),
            installments: Number(form.installments),
            payment_frequency: form.payment_frequency,
            payment_day: Number(form.payment_day),
            second_payment_day:
                form.payment_frequency === 'biweekly'
                    ? Number(form.second_payment_day)
                    : null,
            interest_rate: form.interest_rate === ''
                ? null
                : Number(form.interest_rate),
            monthly_payment: form.monthly_payment === ''
                ? null
                : Number(form.monthly_payment),
            currency: form.currency,
            due_date: form.due_date || null,
            description: form.description.trim() || null,
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
        class="debt-form"
        @submit.prevent="handleSubmit"
    >
        <div class="form-group">
            <label for="debt-name" class="form-label">
                Nombre de la deuda
                <span class="required">*</span>
            </label>

            <input
                id="debt-name"
                v-model="form.name"
                type="text"
                class="form-input"
                :class="{ 'input-error': errors.name }"
                placeholder="Ej: Préstamo del carro"
                maxlength="100"
                :disabled="loading"
            />

            <span v-if="errors.name" class="form-error">
                {{ errors.name }}
            </span>
        </div>

        <div class="form-group">
            <label for="debt-type" class="form-label">
                Tipo de deuda
                <span class="required">*</span>
            </label>

            <select
                id="debt-type"
                v-model="form.type"
                class="form-input"
                :disabled="loading"
            >
                <option
                    v-for="type in debtTypes"
                    :key="type.value"
                    :value="type.value"
                >
                    {{ type.label }}
                </option>
            </select>
        </div>

        <div class="form-group">
            <label for="debt-creditor" class="form-label">
                Acreedor
            </label>

            <input
                id="debt-creditor"
                v-model="form.creditor"
                type="text"
                class="form-input"
                placeholder="Ej: Bancolombia"
                maxlength="150"
                :disabled="loading"
            />

            <span class="form-help">
                Persona o entidad a la que le debes.
            </span>
        </div>

        <div class="form-group">
            <label for="debt-currency" class="form-label">
                Moneda
                <span class="required">*</span>
            </label>

            <select
                id="debt-currency"
                v-model="form.currency"
                class="form-input"
                :disabled="loading"
            >
<option
                    v-for="option in CURRENCIES"
                    :key="option.code"
                    :value="option.code"
                >
                    {{ option.code }} - {{ option.name }}
                </option>
            </select>
        </div>

        <div class="form-group">
            <label for="debt-total" class="form-label">
                Valor total
                <span class="required">*</span>
            </label>

            <div class="input-money">
                <span class="currency-prefix">$</span>

                <input
                    id="debt-total"
                    :value="display.total_amount"
                    type="text"
                    inputmode="decimal"
                    class="form-input money-input"
                    :class="{ 'input-error': errors.total_amount }"
                    placeholder="0"
                    :disabled="loading"
                    @input="handleMoneyInput('total_amount', $event)"
                />
            </div>

            <span v-if="errors.total_amount" class="form-error">
                {{ errors.total_amount }}
            </span>
        </div>

        <div class="form-group">
            <label for="debt-installments" class="form-label">
                Número de cuotas
                <span class="required">*</span>
            </label>

            <input
                id="debt-installments"
                v-model="form.installments"
                type="number"
                class="form-input"
                :class="{ 'input-error': errors.installments }"
                placeholder="12"
                min="1"
                max="600"
                step="1"
                :disabled="loading"
            />

            <span v-if="errors.installments" class="form-error">
                {{ errors.installments }}
            </span>

            <span class="form-help">
                A cuántas cuotas se pactó la deuda.
            </span>
        </div>

        <div class="form-group">
            <label for="debt-frequency" class="form-label">
                Periodicidad de pago
                <span class="required">*</span>
            </label>

            <select
                id="debt-frequency"
                v-model="form.payment_frequency"
                class="form-input"
                :disabled="loading"
            >
                <option value="monthly">Mensual</option>
                <option value="biweekly">Quincenal</option>
            </select>
        </div>

        <div class="form-group">
            <label for="debt-payment-day" class="form-label">
                {{
                    form.payment_frequency === 'biweekly'
                        ? 'Primer día de pago'
                        : 'Día de pago de cada mes'
                }}
                <span class="required">*</span>
            </label>

            <input
                id="debt-payment-day"
                v-model="form.payment_day"
                type="number"
                class="form-input"
                :class="{ 'input-error': errors.payment_day }"
                placeholder="Ej: 15"
                min="1"
                max="31"
                step="1"
                :disabled="loading"
            />

            <span v-if="errors.payment_day" class="form-error">
                {{ errors.payment_day }}
            </span>

            <span class="form-help">
                Si el mes tiene menos días, se paga el último día del mes.
            </span>
        </div>

        <div
            v-if="form.payment_frequency === 'biweekly'"
            class="form-group"
        >
            <label for="debt-second-day" class="form-label">
                Segundo día de pago
                <span class="required">*</span>
            </label>

            <input
                id="debt-second-day"
                v-model="form.second_payment_day"
                type="number"
                class="form-input"
                :class="{ 'input-error': errors.second_payment_day }"
                placeholder="Ej: 30"
                min="1"
                max="31"
                step="1"
                :disabled="loading"
            />

            <span v-if="errors.second_payment_day" class="form-error">
                {{ errors.second_payment_day }}
            </span>
        </div>

        <div class="form-group">
            <label for="debt-monthly" class="form-label">
                Valor de la cuota
            </label>

            <div class="input-money">
                <span class="currency-prefix">$</span>

                <input
                    id="debt-monthly"
                    :value="display.monthly_payment"
                    type="text"
                    inputmode="decimal"
                    class="form-input money-input"
                    placeholder="0"
                    :disabled="loading"
                    @input="handleMoneyInput('monthly_payment', $event)"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="debt-interest" class="form-label">
                Tasa de interés (%)
            </label>

            <input
                id="debt-interest"
                v-model="form.interest_rate"
                type="number"
                class="form-input"
                :class="{ 'input-error': errors.interest_rate }"
                placeholder="0"
                min="0"
                max="999.99"
                step="0.01"
                :disabled="loading"
            />

            <span v-if="errors.interest_rate" class="form-error">
                {{ errors.interest_rate }}
            </span>
        </div>

        <div class="form-group">
            <label for="debt-due-date" class="form-label">
                Fecha de vencimiento
            </label>

            <input
                id="debt-due-date"
                v-model="form.due_date"
                type="date"
                class="form-input"
                :disabled="loading"
            />
        </div>

        <div class="form-group form-group-full">
            <label for="debt-description" class="form-label">
                Descripción
            </label>

            <textarea
                id="debt-description"
                v-model="form.description"
                class="form-input form-textarea"
                placeholder="Agrega una descripción opcional..."
                maxlength="500"
                rows="3"
                :disabled="loading"
            ></textarea>

            <div class="character-count">
                {{ form.description.length }}/500
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
                    {{ debt ? 'Guardar cambios' : 'Guardar deuda' }}
                </span>
            </button>
        </div>
    </form>
</template>

<style scoped src="../styles/debt-form.css"></style>
