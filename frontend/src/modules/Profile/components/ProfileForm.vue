<script setup>
import { reactive, ref } from 'vue'

import {
    formatMoneyInput,
    moneyInputToNumber,
    numberToMoneyInput,
} from '../../../utils/money'

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['submit'])

const PHONE_PATTERN = /^\+?[0-9\s()-]{7,20}$/

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const loading = ref(false)
const errors = ref({})

const form = reactive({
    first_name: props.profile.first_name ?? '',
    last_name: props.profile.last_name ?? '',
    birth_date: props.profile.birth_date ?? '',
    email: props.profile.email ?? '',
    phone: props.profile.phone ?? '',
    address: props.profile.address ?? '',
    employment_type: props.profile.employment_type ?? 'employee',
    contributes_social_security: props.profile.contributes_social_security,
    contract_type: props.profile.contract_type ?? '',
    monthly_income: props.profile.monthly_income ?? '',
    family_reference_name: props.profile.family_reference_name ?? '',
    family_reference_relationship:
        props.profile.family_reference_relationship ?? '',
    family_reference_phone: props.profile.family_reference_phone ?? '',
})

const incomeDisplay = ref(numberToMoneyInput(form.monthly_income))

const today = new Date().toISOString().slice(0, 10)

function handleIncomeInput(event) {
    incomeDisplay.value = formatMoneyInput(event.target.value)
    event.target.value = incomeDisplay.value
    form.monthly_income = moneyInputToNumber(incomeDisplay.value)
}

function validate() {
    errors.value = {}

    const isIndependent = form.employment_type === 'independent'

    if (!form.first_name.trim()) {
        errors.value.first_name = 'Los nombres son obligatorios.'
    }

    if (!form.last_name.trim()) {
        errors.value.last_name = 'Los apellidos son obligatorios.'
    }

    if (form.birth_date && form.birth_date >= today) {
        errors.value.birth_date =
            'La fecha de nacimiento debe ser anterior a hoy.'
    }

    if (!EMAIL_PATTERN.test(form.email.trim())) {
        errors.value.email = 'Ingresa un correo electrónico válido.'
    }

    if (form.phone && !PHONE_PATTERN.test(form.phone)) {
        errors.value.phone = 'El número de teléfono no es válido.'
    }

    if (
        form.family_reference_phone &&
        !PHONE_PATTERN.test(form.family_reference_phone)
    ) {
        errors.value.family_reference_phone =
            'El teléfono de la referencia no es válido.'
    }

    if (isIndependent) {
        if (form.contributes_social_security === null) {
            errors.value.contributes_social_security =
                'Indica si cotizas a seguridad social.'
        }

        if (form.monthly_income === '') {
            errors.value.monthly_income = 'Indica tus ingresos mensuales.'
        }
    } else if (!form.contract_type) {
        errors.value.contract_type = 'Selecciona el tipo de contrato.'
    }

    return Object.keys(errors.value).length === 0
}

function toNullable(value) {
    const trimmed = value.trim()

    return trimmed === '' ? null : trimmed
}

async function handleSubmit() {
    if (!validate()) {
        return
    }

    const isIndependent = form.employment_type === 'independent'

    loading.value = true

    try {
        await emit('submit', {
            first_name: form.first_name.trim(),
            last_name: form.last_name.trim(),
            birth_date: form.birth_date || null,
            email: form.email.trim(),
            phone: toNullable(form.phone),
            address: toNullable(form.address),
            employment_type: form.employment_type,
            contributes_social_security: isIndependent
                ? form.contributes_social_security
                : null,
            contract_type: isIndependent ? null : form.contract_type,
            monthly_income:
                form.monthly_income === ''
                    ? null
                    : Number(form.monthly_income),
            family_reference_name: toNullable(form.family_reference_name),
            family_reference_relationship: toNullable(
                form.family_reference_relationship
            ),
            family_reference_phone: toNullable(form.family_reference_phone),
        })
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <form
        class="profile-form"
        @submit.prevent="handleSubmit"
    >

        <!-- PERSONAL -->

        <h3 class="form-section-title">
            Información personal
        </h3>

        <div class="form-group">
            <label for="profile-first-name" class="form-label">
                Nombres
                <span class="required">*</span>
            </label>

            <input
                id="profile-first-name"
                v-model="form.first_name"
                type="text"
                class="form-input"
                :class="{ 'input-error': errors.first_name }"
                maxlength="100"
                autocomplete="given-name"
                :disabled="loading"
            />

            <span v-if="errors.first_name" class="form-error">
                {{ errors.first_name }}
            </span>
        </div>

        <div class="form-group">
            <label for="profile-last-name" class="form-label">
                Apellidos
                <span class="required">*</span>
            </label>

            <input
                id="profile-last-name"
                v-model="form.last_name"
                type="text"
                class="form-input"
                :class="{ 'input-error': errors.last_name }"
                maxlength="100"
                autocomplete="family-name"
                :disabled="loading"
            />

            <span v-if="errors.last_name" class="form-error">
                {{ errors.last_name }}
            </span>
        </div>

        <div class="form-group">
            <label for="profile-birth-date" class="form-label">
                Fecha de nacimiento
            </label>

            <input
                id="profile-birth-date"
                v-model="form.birth_date"
                type="date"
                class="form-input"
                :class="{ 'input-error': errors.birth_date }"
                :max="today"
                autocomplete="bday"
                :disabled="loading"
            />

            <span v-if="errors.birth_date" class="form-error">
                {{ errors.birth_date }}
            </span>
        </div>

        <!-- EMPLOYMENT -->

        <h3 class="form-section-title">
            Información laboral
        </h3>

        <div class="form-group">
            <label for="profile-employment" class="form-label">
                Situación laboral
                <span class="required">*</span>
            </label>

            <select
                id="profile-employment"
                v-model="form.employment_type"
                class="form-input"
                :disabled="loading"
            >
                <option value="employee">Empleado</option>
                <option value="independent">Independiente</option>
            </select>
        </div>

        <div
            v-if="form.employment_type === 'employee'"
            class="form-group"
        >
            <label for="profile-contract" class="form-label">
                Tipo de contrato
                <span class="required">*</span>
            </label>

            <select
                id="profile-contract"
                v-model="form.contract_type"
                class="form-input"
                :class="{ 'input-error': errors.contract_type }"
                :disabled="loading"
            >
                <option value="" disabled>
                    Selecciona un tipo
                </option>

                <option value="fixed_term">Término fijo</option>
                <option value="indefinite">Indefinido</option>
            </select>

            <span v-if="errors.contract_type" class="form-error">
                {{ errors.contract_type }}
            </span>
        </div>

        <div
            v-else
            class="form-group"
        >
            <span class="form-label">
                ¿Cotizas a seguridad social?
                <span class="required">*</span>
            </span>

            <div class="segmented" role="group">
                <button
                    type="button"
                    class="segmented-option"
                    :class="{ active: form.contributes_social_security === true }"
                    :disabled="loading"
                    @click="form.contributes_social_security = true"
                >
                    Sí
                </button>

                <button
                    type="button"
                    class="segmented-option"
                    :class="{ active: form.contributes_social_security === false }"
                    :disabled="loading"
                    @click="form.contributes_social_security = false"
                >
                    No
                </button>
            </div>

            <span
                v-if="errors.contributes_social_security"
                class="form-error"
            >
                {{ errors.contributes_social_security }}
            </span>
        </div>

        <div class="form-group">
            <label for="profile-income" class="form-label">
                Ingresos mensuales
                <span
                    v-if="form.employment_type === 'independent'"
                    class="required"
                >*</span>
            </label>

            <div class="input-money">
                <span class="currency-prefix">$</span>

                <input
                    id="profile-income"
                    :value="incomeDisplay"
                    type="text"
                    inputmode="decimal"
                    class="form-input money-input"
                    :class="{ 'input-error': errors.monthly_income }"
                    placeholder="0"
                    :disabled="loading"
                    @input="handleIncomeInput"
                />
            </div>

            <span v-if="errors.monthly_income" class="form-error">
                {{ errors.monthly_income }}
            </span>
        </div>

        <!-- CONTACT -->

        <h3 class="form-section-title">
            Contacto
        </h3>

        <div class="form-group">
            <label for="profile-email" class="form-label">
                Correo electrónico
                <span class="required">*</span>
            </label>

            <input
                id="profile-email"
                v-model="form.email"
                type="email"
                class="form-input"
                :class="{ 'input-error': errors.email }"
                maxlength="150"
                autocomplete="email"
                :disabled="loading"
            />

            <span v-if="errors.email" class="form-error">
                {{ errors.email }}
            </span>
        </div>

        <div class="form-group">
            <label for="profile-phone" class="form-label">
                Teléfono
            </label>

            <input
                id="profile-phone"
                v-model="form.phone"
                type="tel"
                class="form-input"
                :class="{ 'input-error': errors.phone }"
                placeholder="Ej: +57 300 123 4567"
                maxlength="20"
                autocomplete="tel"
                :disabled="loading"
            />

            <span v-if="errors.phone" class="form-error">
                {{ errors.phone }}
            </span>
        </div>

        <div class="form-group">
            <label for="profile-address" class="form-label">
                Dirección
            </label>

            <input
                id="profile-address"
                v-model="form.address"
                type="text"
                class="form-input"
                maxlength="200"
                autocomplete="street-address"
                :disabled="loading"
            />
        </div>

        <!-- FAMILY REFERENCE -->

        <h3 class="form-section-title">
            Referencia familiar
        </h3>

        <div class="form-group">
            <label for="profile-ref-name" class="form-label">
                Nombre completo
            </label>

            <input
                id="profile-ref-name"
                v-model="form.family_reference_name"
                type="text"
                class="form-input"
                maxlength="100"
                :disabled="loading"
            />
        </div>

        <div class="form-group">
            <label for="profile-ref-relationship" class="form-label">
                Parentesco
            </label>

            <input
                id="profile-ref-relationship"
                v-model="form.family_reference_relationship"
                type="text"
                class="form-input"
                placeholder="Ej: Madre, hermano..."
                maxlength="50"
                :disabled="loading"
            />
        </div>

        <div class="form-group">
            <label for="profile-ref-phone" class="form-label">
                Teléfono
            </label>

            <input
                id="profile-ref-phone"
                v-model="form.family_reference_phone"
                type="tel"
                class="form-input"
                :class="{ 'input-error': errors.family_reference_phone }"
                maxlength="20"
                :disabled="loading"
            />

            <span
                v-if="errors.family_reference_phone"
                class="form-error"
            >
                {{ errors.family_reference_phone }}
            </span>
        </div>

        <div class="form-actions">
            <button
                type="submit"
                class="btn btn-primary"
                :disabled="loading"
            >
                {{ loading ? 'Guardando...' : 'Guardar cambios' }}
            </button>
        </div>

    </form>
</template>

<style scoped src="../styles/profile-form.css"></style>
