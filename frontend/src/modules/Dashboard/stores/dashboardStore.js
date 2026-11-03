import { defineStore } from 'pinia'
import { ref } from 'vue'

import { dashboardService } from '../services/dashboardService'

function currentPeriod() {
    const today = new Date()

    return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}`
}

export const useDashboardStore = defineStore('dashboard', () => {
    const data = ref(null)

    const loading = ref(false)

    const error = ref(null)

    const period = ref(currentPeriod())

    // null = todas las cuentas activas.
    const accountId = ref(null)

    async function fetchSummary() {
        loading.value = true
        error.value = null

        try {
            const response = await dashboardService.getSummary({
                period: period.value,
                account_id: accountId.value ?? undefined,
            })

            data.value = response.data
        } catch (err) {
            // La cuenta filtrada pudo eliminarse o desactivarse: se vuelve a todas.
            if (err.response?.status === 422 && accountId.value !== null) {
                accountId.value = null

                return fetchSummary()
            }

            error.value =
                err.response?.data?.message ??
                'No fue posible cargar el resumen.'
        } finally {
            loading.value = false
        }
    }

    function setFilters(filters) {
        if ('period' in filters) {
            period.value = filters.period
        }

        if ('accountId' in filters) {
            accountId.value = filters.accountId
        }

        return fetchSummary()
    }

    function resetFilters() {
        return setFilters({ period: currentPeriod(), accountId: null })
    }

    return {
        data,
        loading,
        error,
        period,
        accountId,
        currentPeriod,
        fetchSummary,
        setFilters,
        resetFilters,
    }
})
