import { defineStore } from 'pinia'
import { ref } from 'vue'

import { incomeService } from '../services/incomeService'

export const useIncomeStore = defineStore('incomes', () => {
    const incomes = ref([])

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

    async function fetchIncomes(page = meta.value.current_page) {
        loading.value = true
        error.value = null

        try {
            const response = await incomeService.getAll(page)

            // Si la página quedó vacía (p. ej. tras eliminar), retrocede.
            if (
                response.data.length === 0 &&
                response.meta.current_page > 1
            ) {
                return await fetchIncomes(response.meta.last_page)
            }

            incomes.value = response.data
            meta.value = response.meta
        } catch (err) {
            error.value =
                err.response?.data?.message ??
                'No fue posible cargar los ingresos.'
        } finally {
            loading.value = false
        }
    }

    async function createIncome(data) {
        const response = await incomeService.create(data)

        await fetchIncomes(1)

        return response.data
    }

    async function updateIncome(id, data) {
        const response = await incomeService.update(id, data)

        await fetchIncomes()

        return response.data
    }

    async function deleteIncome(id) {
        await incomeService.delete(id)

        await fetchIncomes()
    }

    return {
        incomes,
        meta,
        loading,
        error,
        fetchIncomes,
        createIncome,
        updateIncome,
        deleteIncome,
    }
})
