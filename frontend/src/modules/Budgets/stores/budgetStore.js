import { defineStore } from 'pinia'
import { ref } from 'vue'

import { budgetService } from '../services/budgetService'

function currentPeriod() {
    const now = new Date()

    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
}

export const useBudgetStore = defineStore('budgets', () => {
    const period = ref(currentPeriod())

    const overview = ref(null)

    const loading = ref(false)

    const error = ref(null)

    async function fetchOverview(targetPeriod = period.value) {
        period.value = targetPeriod
        loading.value = true
        error.value = null

        try {
            const response = await budgetService.get(targetPeriod)

            overview.value = response.data
        } catch (err) {
            error.value =
                err.response?.data?.message ??
                'No fue posible cargar el presupuesto.'
        } finally {
            loading.value = false
        }
    }

    async function saveBudget(data) {
        const response = await budgetService.save({
            period: period.value,
            ...data,
        })

        overview.value = response.data
    }

    async function resetBudget() {
        const response = await budgetService.reset(period.value)

        overview.value = response.data
    }

    return {
        period,
        overview,
        loading,
        error,
        fetchOverview,
        saveBudget,
        resetBudget,
    }
})
