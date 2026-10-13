import { defineStore } from 'pinia'
import { ref } from 'vue'

import { expenseService } from '../services/expenseService'

export const useExpenseStore = defineStore('expenses', () => {
    const expenses = ref([])

    const meta = ref({
        current_page: 1,
        last_page: 1,
        per_page: 20,
        total: 0,
        from: null,
        to: null,
    })

    const loading = ref(false)

    const error = ref(null)

    async function fetchExpenses(page = meta.value.current_page) {
        loading.value = true
        error.value = null

        try {
            const response = await expenseService.getAll(page)

            // Si la página quedó vacía (p. ej. tras eliminar), retrocede.
            if (
                response.data.length === 0 &&
                response.meta.current_page > 1
            ) {
                return await fetchExpenses(response.meta.last_page)
            }

            expenses.value = response.data
            meta.value = response.meta
        } catch (err) {
            error.value =
                err.response?.data?.message ??
                'No fue posible cargar los gastos.'
        } finally {
            loading.value = false
        }
    }

    async function createExpense(data) {
        const response = await expenseService.create(data)

        await fetchExpenses(1)

        return response.data
    }

    async function updateExpense(id, data) {
        const response = await expenseService.update(id, data)

        await fetchExpenses()

        return response.data
    }

    async function deleteExpense(id) {
        await expenseService.delete(id)

        await fetchExpenses()
    }

    return {
        expenses,
        meta,
        loading,
        error,
        fetchExpenses,
        createExpense,
        updateExpense,
        deleteExpense,
    }
})
