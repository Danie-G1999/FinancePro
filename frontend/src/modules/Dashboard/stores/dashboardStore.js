import { defineStore } from 'pinia'
import { ref } from 'vue'

import { dashboardService } from '../services/dashboardService'

export const useDashboardStore = defineStore('dashboard', () => {
    const data = ref(null)

    const loading = ref(false)

    const error = ref(null)

    async function fetchSummary() {
        loading.value = true
        error.value = null

        try {
            const response = await dashboardService.getSummary()

            data.value = response.data
        } catch (err) {
            error.value =
                err.response?.data?.message ??
                'No fue posible cargar el resumen.'
        } finally {
            loading.value = false
        }
    }

    return {
        data,
        loading,
        error,
        fetchSummary,
    }
})
