<script setup>
import { computed, onMounted, ref } from 'vue'

import ExpenseCategoryFormModal from '../components/ExpenseCategoryFormModal.vue'
import { useExpenseCategoryStore } from '../stores/expenseCategoryStore'
import { useBudgetTypes } from '../utils/budgetTypes'
import {
    confirmAction,
    extractErrorMessage,
    showError,
    showSuccess,
    withLoading,
} from '../../../utils/alerts'

const categoryStore = useExpenseCategoryStore()

const { budgetTypes } = useBudgetTypes()

const showForm = ref(false)

const editingCategory = ref(null)

const defaultBudgetType = ref('')

const showInactive = ref(false)

const columns = computed(() =>
    budgetTypes.value.map((type) => ({
        ...type,
        categories: categoryStore.categories.filter(
            (category) =>
                category.budget_type === type.value &&
                (showInactive.value || category.is_active)
        ),
    }))
)

const inactiveCount = computed(
    () => categoryStore.categories.filter((category) => !category.is_active).length
)

function openCreate(budgetType = '') {
    editingCategory.value = null
    defaultBudgetType.value = budgetType
    showForm.value = true
}

function openEdit(category) {
    editingCategory.value = category
    showForm.value = true
}

function handleCancel() {
    showForm.value = false
    editingCategory.value = null
}

async function handleSave(data) {
    const isEditing = editingCategory.value !== null

    try {
        await withLoading(
            isEditing ? 'Guardando cambios...' : 'Creando categoría...',
            () =>
                isEditing
                    ? categoryStore.updateCategory(editingCategory.value.id, data)
                    : categoryStore.createCategory(data)
        )

        handleCancel()

        showSuccess(
            isEditing
                ? 'Categoría actualizada correctamente.'
                : 'Categoría creada correctamente.'
        )
    } catch (error) {
        showError(
            extractErrorMessage(error, 'No fue posible guardar la categoría.')
        )
    }
}

async function toggleActive(category) {
    try {
        await categoryStore.updateCategory(category.id, {
            name: category.name,
            budget_type: category.budget_type,
            icon: category.icon,
            color: category.color,
            is_active: !category.is_active,
        })
    } catch (error) {
        showError(
            extractErrorMessage(error, 'No fue posible actualizar la categoría.')
        )
    }
}

async function askDelete(category) {
    const confirmed = await confirmAction({
        title: 'Eliminar categoría',
        text: category.expenses_count
            ? `"${category.name}" tiene ${category.expenses_count} gasto(s) registrados, así que se desactivará para conservar tu historial.`
            : `¿Seguro que deseas eliminar "${category.name}"? Esta acción no se puede deshacer.`,
        confirmText: category.expenses_count ? 'Desactivar' : 'Eliminar',
        danger: true,
    })

    if (!confirmed) {
        return
    }

    try {
        const response = await withLoading('Procesando...', () =>
            categoryStore.deleteCategory(category.id)
        )

        showSuccess(response.message)
    } catch (error) {
        showError(
            extractErrorMessage(error, 'No fue posible eliminar la categoría.')
        )
    }
}

onMounted(() => {
    categoryStore.fetchCategories()
})
</script>

<template>
    <div class="categories-page">

        <!-- HEADER -->

        <header class="page-header">
            <div>
                <h1 class="page-title">
                    Categorías de gasto
                </h1>

                <p class="page-description">
                    Organiza tus gastos dentro de cada bloque de la regla 50/30/20.
                </p>
            </div>

            <div class="header-actions">
                <label
                    v-if="inactiveCount > 0"
                    class="inactive-toggle"
                >
                    <input
                        v-model="showInactive"
                        type="checkbox"
                    />

                    Mostrar inactivas ({{ inactiveCount }})
                </label>

                <button
                    type="button"
                    class="btn btn-primary"
                    @click="openCreate()"
                >
                    + Nueva categoría
                </button>
            </div>
        </header>


        <!-- LOADING -->

        <div
            v-if="categoryStore.loading && categoryStore.categories.length === 0"
            class="state-container"
        >
            <div class="loading-spinner"></div>

            <p>
                Cargando categorías...
            </p>
        </div>


        <!-- ERROR -->

        <div
            v-else-if="categoryStore.error"
            class="alert alert-error"
        >
            {{ categoryStore.error }}
        </div>


        <!-- COLUMNS -->

        <section
            v-else
            class="categories-grid"
        >
            <article
                v-for="column in columns"
                :key="column.value"
                class="budget-column"
                :style="{ '--block-color': column.color }"
            >
                <header class="budget-column-header">
                    <span class="budget-column-title">
                        {{ column.label }}
                    </span>

                    <span class="budget-column-count">
                        {{ column.categories.length }}
                    </span>
                </header>

                <ul
                    v-if="column.categories.length > 0"
                    class="category-list"
                >
                    <li
                        v-for="category in column.categories"
                        :key="category.id"
                        class="category-item"
                        :class="{ 'category-inactive': !category.is_active }"
                    >
                        <span
                            class="category-icon"
                            :style="{ background: category.color ?? column.color }"
                        >
                            {{ category.icon || category.name.charAt(0) }}
                        </span>

                        <div class="category-info">
                            <span class="category-name">
                                {{ category.name }}
                            </span>

                            <span class="category-meta">
                                {{ category.expenses_count }} gasto(s)
                                <template v-if="!category.is_active">· Inactiva</template>
                            </span>
                        </div>

                        <div class="category-actions">
                            <button
                                type="button"
                                class="icon-button"
                                :title="category.is_active ? 'Desactivar' : 'Activar'"
                                @click="toggleActive(category)"
                            >
                                <svg v-if="category.is_active" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>

                                <svg v-else viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 3l18 18" />
                                    <path d="M10.6 5.1A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.2M6.6 6.6C3.9 8.4 2 12 2 12s3.5 7 10 7a10 10 0 0 0 5.4-1.6" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                class="icon-button"
                                title="Editar"
                                @click="openEdit(category)"
                            >
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                class="icon-button danger"
                                title="Eliminar"
                                @click="askDelete(category)"
                            >
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                </svg>
                            </button>
                        </div>
                    </li>
                </ul>

                <p
                    v-else
                    class="column-empty"
                >
                    Aún no hay categorías en este bloque.
                </p>

                <button
                    type="button"
                    class="add-in-column"
                    @click="openCreate(column.value)"
                >
                    + Agregar categoría
                </button>
            </article>
        </section>


        <!-- FORM MODAL -->

        <ExpenseCategoryFormModal
            v-if="showForm"
            :key="editingCategory?.id ?? 'new'"
            :category="editingCategory"
            :default-budget-type="defaultBudgetType"
            @submit="handleSave"
            @cancel="handleCancel"
        />

    </div>
</template>

<style scoped src="../styles/expense-categories-view.css"></style>
