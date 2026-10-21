<script setup>
import { reactive, ref } from 'vue'
import { CURRENCIES } from '../../../utils/currencies'
import { useBudgetTypes } from '../../ExpenseCategories/utils/budgetTypes'

import BaseModal from '../../../components/common/BaseModal.vue'
import { formatMoney } from '../../../utils/money'
import {
    formatMoneyInput,
    moneyInputToNumber,
    numberToMoneyInput,
} from '../../../utils/money'

const { ruleLabel } = useBudgetTypes()

const props = defineProps({
    overview: {
        type: Object,
        required: true,
    },

    monthLabel: {
        type: String,
        required: true,
    },
})

const emit = defineEmits(['submit', 'reset', 'cancel'])

const loading = ref(false)
const errors = ref({})

const form = reactive({
    net_income: props.overview.income.planned ?? props.overview.income.registered ?? '',
    currency: props.overview.currency,
})

const display = reactive({
    net_income: form.net_income ? numberToMoneyInput(String(form.net_income)) : '',
})

function handleIncomeInput(event) {
    display.net_income = formatMoneyInput(event.target.value)
    event.target.value = display.net_income
    form.net_income = moneyInputToNumber(display.net_income)
}

async function run(action) {
    loading.value = true

    try {
        await action()
    } finally {
        loading.value = false
    }
}

function handleSubmit() {
    errors.value = {}

    if (!form.net_income || Number(form.net_income) <= 0) {
        errors.value.net_income = 'El ingreso neto debe ser mayor a 0.'

        return
    }

    return run(() =>
        emit('submit', {
            net_income: Number(form.net_income),
            currency: form.currency,
        })
    )
}

function handleReset() {
    return run(() => emit('reset'))
}

function handleCancel() {
    if (!loading.value) {
        emit('cancel')
    }
}
</script>

<template>
    <BaseModal
        title="Ingreso neto del mes"
        :subtitle="monthLabel"
        :persistent="loading"
        @close="handleCancel"
    >
        <form
            class="income-form"
            @submit.prevent="handleSubmit"
        >
            <p class="hint">
                Tu reparto {{ ruleLabel }} se calcula sobre este valor. Si no lo
                defines, se usan tus ingresos registrados en el mes
                ({{ formatMoney(overview.income.registered, overview.currency) }}).
            </p>

            <div class="form-group">
                <label for="budget-income" class="form-label">
                    Ingreso neto
                    <span class="required">*</span>
                </label>

                <div class="input-money">
                    <span class="currency-prefix">$</span>

                    <input
                        id="budget-income"
                        :value="display.net_income"
                        type="text"
                        inputmode="decimal"
                        class="form-input money-input"
                        :class="{ 'input-error': errors.net_income }"
                        placeholder="0"
                        :disabled="loading"
                        @input="handleIncomeInput"
                    />
                </div>

                <span v-if="errors.net_income" class="form-error">
                    {{ errors.net_income }}
                </span>
            </div>

            <div class="form-group">
                <label for="budget-currency" class="form-label">
                    Moneda
                    <span class="required">*</span>
                </label>

                <select
                    id="budget-currency"
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

                <span class="form-help">
                    Solo se cuentan los gastos de cuentas en esta moneda.
                </span>
            </div>

            <div class="form-actions">
                <button
                    v-if="overview.income.planned !== null"
                    type="button"
                    class="btn btn-link"
                    :disabled="loading"
                    @click="handleReset"
                >
                    Usar ingresos registrados
                </button>

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
                    {{ loading ? 'Guardando...' : 'Guardar' }}
                </button>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped src="../styles/budget-income-form.css"></style>
