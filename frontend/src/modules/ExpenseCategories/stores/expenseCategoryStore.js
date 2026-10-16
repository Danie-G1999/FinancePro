import { defineStore } from 'pinia'
import { ref } from 'vue'

import { expenseCategoryService } from '../services/expenseCategoryService'

export const useExpenseCategoryStore = defineStore('expenseCategories', () => {
    // Incluye las inactivas: el formulario de gastos las necesita al editar gastos antiguos.
    const categories = ref([])

    const loading = ref(false)

    const error = ref(null)

    async function fetchCategories() {
        loading.value = true
        error.value = null

        try {
            const response = await expenseCategoryService.getAll()

            categories.value = response.data
        } catch (err) {
            error.value =
                err.response?.data?.message ??
                'No fue posible cargar las categorías de gasto.'
        } finally {
            loading.value = false
        }
    }

    async function createCategory(data) {
        const response = await expenseCategoryService.create(data)

        await fetchCategories()

        return response.data
    }

    async function updateCategory(id, data) {
        const response = await expenseCategoryService.update(id, data)

        await fetchCategories()

        return response.data
    }

    // Devuelve la respuesta para saber si se eliminó o solo se desactivó.
    async function deleteCategory(id) {
        const response = await expenseCategoryService.delete(id)

        await fetchCategories()

        return response
    }

    return {
        categories,
        loading,
        error,
        fetchCategories,
        createCategory,
        updateCategory,
        deleteCategory,
    }
})
