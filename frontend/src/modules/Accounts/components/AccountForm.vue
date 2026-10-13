<script setup>
import { reactive, ref } from 'vue'

const emit = defineEmits(['submit', 'cancel'])

const loading = ref(false)
const errors = ref({})

const form = reactive({
    name: '',
    type: 'bank',
    institution: '',
    initial_balance: '',
    currency: 'COP',
    is_active: true,
    description: '',
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
                    v-model="form.initial_balance"
                    type="number"
                    class="form-input money-input"
                    :class="{
                        'input-error': errors.initial_balance,
                    }"
                    placeholder="0"
                    min="0"
                    step="0.01"
                    :disabled="loading"
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
                <option value="COP">
                    COP - Peso colombiano
                </option>

                <option value="USD">
                    USD - Dólar estadounidense
                </option>

                <option value="EUR">
                    EUR - Euro
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
        <div class="form-group">
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
        <div class="form-group">
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
                    Guardar cuenta
                </span>
            </button>
        </div>
    </form>
</template>

<style scoped>
.account-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.form-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-text-primary);
}

.required {
    color: var(--color-danger);
    margin-left: 2px;
}

.form-input {
    min-height: 42px;
    width: 100%;

    padding: 0 12px;

    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);

    background: var(--color-surface);
    color: var(--color-text-primary);

    font-size: 13px;

    transition:
        border-color var(--transition-fast),
        box-shadow var(--transition-fast);
}

.form-input:hover {
    border-color: var(--color-border-dark);
}

.form-input:focus {
    border-color: var(--color-primary);

    box-shadow:
        0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-input:disabled {
    background: #f9fafb;
    cursor: not-allowed;
}

.input-error {
    border-color: var(--color-danger);
}

.input-error:focus {
    border-color: var(--color-danger);

    box-shadow:
        0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-error {
    color: var(--color-danger);
    font-size: 11px;
}

.form-help {
    color: var(--color-text-muted);
    font-size: 11px;
    line-height: 1.5;
}

.input-money {
    position: relative;
}

.currency-prefix {
    position: absolute;
    left: 12px;
    top: 50%;

    transform: translateY(-50%);

    color: var(--color-text-muted);

    font-size: 13px;
    font-weight: 500;

    z-index: 1;
}

.money-input {
    padding-left: 28px;
}

.form-textarea {
    min-height: 90px;
    padding: 10px 12px;

    resize: vertical;
}

.character-count {
    align-self: flex-end;

    margin-top: -3px;

    color: var(--color-text-light);

    font-size: 10px;
}

/* =========================================================
   SWITCH
   ========================================================= */

.switch-container {
    display: flex;
    align-items: center;

    gap: 10px;

    cursor: pointer;
}

.switch-container input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.switch {
    position: relative;

    width: 40px;
    height: 22px;

    flex-shrink: 0;

    border-radius: 999px;

    background: #d1d5db;

    transition:
        background var(--transition-fast);
}

.switch::after {
    content: '';

    position: absolute;

    width: 16px;
    height: 16px;

    top: 3px;
    left: 3px;

    border-radius: 50%;

    background: white;

    box-shadow: var(--shadow-sm);

    transition:
        transform var(--transition-fast);
}

.switch-container input:checked + .switch {
    background: var(--color-primary);
}

.switch-container input:checked + .switch::after {
    transform: translateX(18px);
}

.switch-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.switch-label {
    color: var(--color-text-primary);

    font-size: 13px;
    font-weight: 500;
}

.switch-help {
    color: var(--color-text-muted);

    font-size: 11px;
}

/* =========================================================
   ACTIONS
   ========================================================= */

.form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;

    gap: 10px;

    padding-top: 8px;

    border-top: 1px solid var(--color-border-light);
}

.btn {
    min-height: 40px;

    padding: 0 16px;

    border: 1px solid transparent;
    border-radius: var(--radius-md);

    font-size: 13px;
    font-weight: 600;

    transition:
        background var(--transition-fast),
        border-color var(--transition-fast),
        box-shadow var(--transition-fast);
}

.btn:disabled {
    opacity: 0.6;
}

.btn-primary {
    background: var(--color-primary);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: var(--color-primary-hover);

    box-shadow: var(--shadow-sm);
}

.btn-secondary {
    background: white;
    border-color: var(--color-border);
    color: var(--color-text-secondary);
}

.btn-secondary:hover:not(:disabled) {
    background: var(--color-surface-hover);
    border-color: var(--color-border-dark);
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 640px) {
    .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .btn {
        width: 100%;
    }
}
</style>