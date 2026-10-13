import { defineStore } from 'pinia'
import { ref } from 'vue'

import { debtService } from '../services/debtService'

export const useDebtStore = defineStore('debts', () => {
    const debts = ref([])

    const loading = ref(false)

    const error = ref(null)

    async function fetchDebts() {
        loading.value = true
        error.value = null

        try {
            const response = await debtService.getAll()

            debts.value = response.data
        } catch (err) {
            error.value =
                err.response?.data?.message ??
                'No fue posible cargar las deudas.'
        } finally {
            loading.value = false
        }
    }

    async function createDebt(data) {
        const response = await debtService.create(data)

        debts.value.push(response.data)

        return response.data
    }

    async function updateDebt(id, data) {
        const response = await debtService.update(id, data)

        const index = debts.value.findIndex(
            (debt) => debt.id === id
        )

        if (index !== -1) {
            debts.value[index] = response.data
        }

        return response.data
    }

    async function deleteDebt(id) {
        await debtService.delete(id)

        debts.value = debts.value.filter(
            (debt) => debt.id !== id
        )
    }

    return {
        debts,
        loading,
        error,
        fetchDebts,
        createDebt,
        updateDebt,
        deleteDebt,
    }
})
