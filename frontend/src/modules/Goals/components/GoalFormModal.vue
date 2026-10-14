<script setup>
import { reactive, ref } from 'vue'

import BaseModal from '../../../components/common/BaseModal.vue'
import {
    formatMoneyInput,
    moneyInputToNumber,
    numberToMoneyInput,
} from '../../../utils/money'

const props = defineProps({
    goal: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['submit', 'cancel'])

const loading = ref(false)
const errors = ref({})

const form = reactive({
    name: props.goal?.name ?? '',
    target_amount: props.goal?.target_amount ?? '',
    currency: props.goal?.currency ?? 'COP',
    target_date: props.goal?.target_date ?? '',
    description: props.goal?.description ?? '',
})

const display = reactive({
    target_amount: numberToMoneyInput(form.target_amount),
})

function handleAmountInput(event) {
    display.target_amount = formatMoneyInput(event.target.value)
    event.target.value = display.target_amount
    form.target_amount = moneyInputToNumber(display.target_amount)
}

function validate() {
    errors.value = {}

    if (!form.name.trim()) {
        errors.value.name = 'El nombre de la meta es obligatorio.'
    }

    if (!form.target_amount || Number(form.target_amount) <= 0) {
        errors.value.target_amount = 'El valor objetivo debe ser mayor a 0.'
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
            target_amount: Number(form.target_amount),
            currency: form.currency,
            target_date: form.target_date || null,
            description: form.description.trim() || null,
        })
    } finally {
        loading.value = false
    }
}

function handleCancel() {
    if (!loading.value) {
        emit('cancel')
    }
}
</script>

<template>
    <BaseModal
        :title="goal ? 'Editar meta' : 'Nueva meta de ahorro'"
        subtitle="Define cuánto quieres ahorrar y para cuándo."
        max-width="560px"
        :persistent="loading"
        @close="handleCancel"
    >
        <form
            class="goal-form"
            @submit.prevent="handleSubmit"
        >
            <div class="form-group form-group-full">
                <label for="goal-name" class="form-label">
                    Nombre de la meta
                    <span class="required">*</span>
                </label>

                <input
                    id="goal-name"
                    v-model="form.name"
                    type="text"
                    class="form-input"
                    :class="{ 'input-error': errors.name }"
                    placeholder="Ej: Fondo de emergencia"
                    maxlength="100"
                    :disabled="loading"
                />

                <span v-if="errors.name" class="form-error">
                    {{ errors.name }}
                </span>
            </div>

            <div class="form-group">
                <label for="goal-amount" class="form-label">
                    Valor objetivo
                    <span class="required">*</span>
                </label>

                <div class="input-money">
                    <span class="currency-prefix">$</span>

                    <input
                        id="goal-amount"
                        :value="display.target_amount"
                        type="text"
                        inputmode="decimal"
                        class="form-input money-input"
                        :class="{ 'input-error': errors.target_amount }"
                        placeholder="0"
                        :disabled="loading"
                        @input="handleAmountInput"
                    />
                </div>

                <span v-if="errors.target_amount" class="form-error">
                    {{ errors.target_amount }}
                </span>
            </div>

            <div class="form-group">
                <label for="goal-currency" class="form-label">
                    Moneda
                    <span class="required">*</span>
                </label>

                <select
                    id="goal-currency"
                    v-model="form.currency"
                    class="form-input"
                    :disabled="loading || Boolean(goal)"
                >
                    <option value="COP">COP - Peso colombiano</option>
                    <option value="USD">USD - Dólar estadounidense</option>
                    <option value="EUR">EUR - Euro</option>
                </select>

                <span v-if="goal" class="form-help">
                    La moneda no se puede cambiar después de crear la meta.
                </span>
            </div>

            <div class="form-group form-group-full">
                <label for="goal-date" class="form-label">
                    Fecha objetivo
                </label>

                <input
                    id="goal-date"
                    v-model="form.target_date"
                    type="date"
                    class="form-input"
                    :disabled="loading"
                />
            </div>

            <div class="form-group form-group-full">
                <label for="goal-description" class="form-label">
                    Descripción
                </label>

                <textarea
                    id="goal-description"
                    v-model="form.description"
                    class="form-input form-textarea"
                    maxlength="500"
                    rows="2"
                    :disabled="loading"
                ></textarea>
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
                    {{ loading ? 'Guardando...' : goal ? 'Guardar cambios' : 'Crear meta' }}
                </button>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped src="../styles/goal-form.css"></style>
