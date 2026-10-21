<script setup>
import { reactive, ref } from 'vue'
import { CURRENCIES } from '../../../utils/currencies'
import { useCurrencyFormatter } from '../../../composables/useCurrencyFormatter'

// Las nuevas cuentas, deudas y metas usan la moneda preferida del usuario.
const { preferredCurrency } = useCurrencyFormatter()

const props = defineProps({
    account: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['submit', 'cancel'])

const loading = ref(false)
const errors = ref({})

const form = reactive({
    name: props.account?.name ?? '',
    type: props.account?.type ?? 'bank',
    institution: props.account?.institution ?? '',
    initial_balance: props.account?.initial_balance ?? '',
    currency: props.account?.currency ?? preferredCurrency.value,
    is_active: props.account?.is_active ?? true,
    description: props.account?.description ?? '',
})

const accountTypes = [
    {
        value: 'cash',
        label: 'Efectivo',
    },
    {
        value: 'bank',
        label: 'Cuenta bancaria',
    },
    {
        value: 'credit_card',
        label: 'Tarjeta de crédito',
    },
    {
        value: 'savings',
        label: 'Cuenta de ahorros',
    },
    {
        value: 'investment',
        label: 'Inversión',
    },
    {
        value: 'other',
        label: 'Otro',
    },
]

function formatBalanceInput(raw) {
    const cleaned = String(raw).replace(/[^\d,]/g, '')
    const [integer, ...rest] = cleaned.split(',')
    const decimals = rest.join('').slice(0, 2)
    const grouped = integer
        .replace(/^0+(?=\d)/, '')
        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')

    return cleaned.includes(',')
        ? `${grouped},${decimals}`
        : grouped
}

function toNumericString(display) {
    return display.replace(/\./g, '').replace(',', '.')
}

const balanceDisplay = ref(
    props.account
        ? formatBalanceInput(
              String(props.account.initial_balance).replace('.', ',')
          )
        : ''
)

function handleBalanceInput(event) {
    balanceDisplay.value = formatBalanceInput(event.target.value)
    event.target.value = balanceDisplay.value
    form.initial_balance = toNumericString(balanceDisplay.value)
}

function validate() {
    errors.value = {}

    if (!form.name.trim()) {
        errors.value.name = 'El nombre de la cuenta es obligatorio.'
    }

    if (!form.type) {
        errors.value.type = 'Selecciona un tipo de cuenta.'
    }

    if (
        form.initial_balance === '' ||
        form.initial_balance === null ||
        Number(form.initial_balance) < 0
    ) {
        errors.value.initial_balance =
            'El saldo inicial debe ser mayor o igual a 0.'
    }

    if (!form.currency) {
        errors.value.currency = 'Selecciona una moneda.'
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
            institution: form.institution.trim() || null,
            initial_balance: Number(form.initial_balance),
            currency: form.currency,
            is_active: form.is_active,
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
        class="account-form"
        @submit.prevent="handleSubmit"
    >
        <!-- Nombre -->
        <div class="form-group">
            <label
                for="account-name"
                class="form-label"
            >
                Nombre de la cuenta
                <span class="required">*</span>
            </label>

            <input
                id="account-name"
                v-model="form.name"
                type="text"
                class="form-input"
                :class="{ 'input-error': errors.name }"
                placeholder="Ej: Cuenta principal"
                maxlength="100"
                :disabled="loading"
            />

            <span
                v-if="errors.name"
                class="form-error"
            >
                {{ errors.name }}
            </span>
        </div>

        <!-- Tipo -->
        <div class="form-group">
            <label
                for="account-type"
                class="form-label"
            >
                Tipo de cuenta
                <span class="required">*</span>
            </label>

            <select
                id="account-type"
                v-model="form.type"
                class="form-input"
                :class="{ 'input-error': errors.type }"
                :disabled="loading"
            >
                <option
                    v-for="type in accountTypes"
                    :key="type.value"
                    :value="type.value"
                >
                    {{ type.label }}
                </option>
            </select>

            <span
                v-if="errors.type"
                class="form-error"
            >
                {{ errors.type }}
            </span>
        </div>

        <!-- Institución -->
        <div class="form-group">
            <label
                for="account-institution"
                class="form-label"
            >
                Institución
            </label>

            <input
                id="account-institution"
                v-model="form.institution"
                type="text"
                class="form-input"
                placeholder="Ej: Bancolombia"
                maxlength="150"
                :disabled="loading"
            />

            <span class="form-help">
                Banco, entidad financiera o lugar donde tienes el dinero.
            </span>
        </div>

        <!-- Saldo inicial -->
        <div class="form-group">
            <label
                for="account-balance"
                class="form-label"
            >
                Saldo inicial
                <span class="required">*</span>
            </label>

            <div class="input-money">
                <span class="currency-prefix">
                    $
                </span>

                <input
                    id="account-balance"
                    :value="balanceDisplay"
                    type="text"
                    inputmode="decimal"
                    class="form-input money-input"
                    :class="{
                        'input-error': errors.initial_balance,
                    }"
                    placeholder="0"
                    :disabled="loading"
                    @input="handleBalanceInput"
                />
            </div>

            <span
                v-if="errors.initial_balance"
                class="form-error"
            >
                {{ errors.initial_balance }}
            </span>

            <span class="form-help">
                Valor disponible en la cuenta al momento de registrarla.
            </span>
        </div>

        <!-- Moneda -->
        <div class="form-group">
            <label
                for="account-currency"
                class="form-label"
            >
                Moneda
                <span class="required">*</span>
            </label>

            <select
                id="account-currency"
                v-model="form.currency"
                class="form-input"
                :class="{ 'input-error': errors.currency }"
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

            <span
                v-if="errors.currency"
                class="form-error"
            >
                {{ errors.currency }}
            </span>
        </div>

        <!-- Estado -->
        <div class="form-group form-group-full">
            <label class="switch-container">
                <input
                    v-model="form.is_active"
                    type="checkbox"
                    :disabled="loading"
                />

                <span class="switch"></span>

                <span class="switch-content">
                    <span class="switch-label">
                        Cuenta activa
                    </span>

                    <span class="switch-help">
                        Puedes desactivarla cuando ya no la utilices.
                    </span>
                </span>
            </label>
        </div>

        <!-- Descripción -->
        <div class="form-group form-group-full">
            <label
                for="account-description"
                class="form-label"
            >
                Descripción
            </label>

            <textarea
                id="account-description"
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

        <!-- Acciones -->
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
                <span v-if="loading">
                    Guardando...
                </span>

                <span v-else>
                    {{ account ? 'Guardar cambios' : 'Guardar cuenta' }}
                </span>
            </button>
        </div>
    </form>
</template>

<style scoped src="../styles/account-form.css"></style>