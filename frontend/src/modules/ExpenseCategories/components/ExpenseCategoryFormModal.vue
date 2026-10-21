<script setup>
import { reactive, ref, watch } from 'vue'

import BaseModal from '../../../components/common/BaseModal.vue'
import IconPicker from './IconPicker.vue'
import { budgetTypeColor, useBudgetTypes } from '../utils/budgetTypes'

const props = defineProps({
    category: {
        type: Object,
        default: null,
    },

    // Bloque preseleccionado al crear desde la columna de un bloque.
    defaultBudgetType: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['submit', 'cancel'])

const { budgetTypes } = useBudgetTypes()

const loading = ref(false)
const errors = ref({})

const form = reactive({
    name: props.category?.name ?? '',
    budget_type: props.category?.budget_type ?? props.defaultBudgetType,
    icon: props.category?.icon ?? '',
    color: props.category?.color ?? budgetTypeColor(props.defaultBudgetType),
    is_active: props.category?.is_active ?? true,
})

// Mientras el usuario no elija un color propio, sigue el color del bloque.
const colorTouched = ref(Boolean(props.category?.color))

watch(
    () => form.budget_type,
    (value) => {
        if (!colorTouched.value) {
            form.color = budgetTypeColor(value)
        }
    }
)

function validate() {
    errors.value = {}

    if (!form.name.trim()) {
        errors.value.name = 'El nombre de la categoría es obligatorio.'
    }

    if (!form.budget_type) {
        errors.value.budget_type = 'Selecciona la categoría del presupuesto.'
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
            budget_type: form.budget_type,
            icon: form.icon.trim() || null,
            color: form.color || null,
            is_active: form.is_active,
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
        :title="category ? 'Editar categoría' : 'Nueva categoría de gasto'"
        subtitle="Agrupa tus gastos dentro de la regla 50/30/20."
        max-width="560px"
        :persistent="loading"
        @close="handleCancel"
    >
        <form
            class="category-form"
            @submit.prevent="handleSubmit"
        >
            <div class="form-group form-group-full">
                <label for="category-name" class="form-label">
                    Nombre
                    <span class="required">*</span>
                </label>

                <input
                    id="category-name"
                    v-model="form.name"
                    type="text"
                    class="form-input"
                    :class="{ 'input-error': errors.name }"
                    placeholder="Ej: Mascotas"
                    maxlength="60"
                    :disabled="loading"
                />

                <span v-if="errors.name" class="form-error">
                    {{ errors.name }}
                </span>
            </div>

            <div class="form-group">
                <label for="category-budget-type" class="form-label">
                    Categoría del presupuesto
                    <span class="required">*</span>
                </label>

                <select
                    id="category-budget-type"
                    v-model="form.budget_type"
                    class="form-input"
                    :class="{ 'input-error': errors.budget_type }"
                    :disabled="loading"
                >
                    <option value="" disabled>
                        Selecciona una regla del presupuesto
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

                <span
                    v-if="category?.expenses_count"
                    class="form-help"
                >
                    Tiene {{ category.expenses_count }} gasto(s). Cambiar el bloque
                    no reclasifica los gastos ya registrados.
                </span>
            </div>

            <div class="form-group">
                <label for="category-color" class="form-label">
                    Color
                </label>

                <input
                    id="category-color"
                    v-model="form.color"
                    type="color"
                    class="form-input color-input"
                    :disabled="loading"
                    @input="colorTouched = true"
                />
            </div>

            <div class="form-group form-group-full">
                <label for="category-icon" class="form-label">
                    Ícono
                </label>

                <IconPicker
                    id="category-icon"
                    v-model="form.icon"
                    :disabled="loading"
                />

                <span class="form-help">
                    Opcional. Elige uno de la lista o pega cualquier emoji.
                </span>
            </div>

            <label class="switch-container form-group-full">
                <input
                    v-model="form.is_active"
                    type="checkbox"
                    :disabled="loading"
                />

                <span class="switch"></span>

                <span class="switch-content">
                    <span class="switch-label">Categoría activa</span>

                    <span class="switch-help">
                        Las inactivas no aparecen al registrar nuevos gastos.
                    </span>
                </span>
            </label>

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
                    {{ loading ? 'Guardando...' : category ? 'Guardar cambios' : 'Crear categoría' }}
                </button>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped src="../styles/expense-category-form.css"></style>
