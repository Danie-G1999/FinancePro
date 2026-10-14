import { defineStore } from 'pinia'
import { ref } from 'vue'

import { reportService } from '../services/reportService'
import { getPresetRange } from '../utils/periods'

export const useReportStore = defineStore('reports', () => {
    const preset = ref('this_month')

    const range = ref(getPresetRange('this_month'))

    const currency = ref('')

    const report = ref(null)

    const loading = ref(false)

    const error = ref(null)

    async function fetchReport() {
        loading.value = true
        error.value = null

        try {
            const response = await reportService.get({
                date_from: range.value.from,
                date_to: range.value.to,
                ...(currency.value ? { currency: currency.value } : {}),
            })

            report.value = response.data

            // La moneda efectiva puede diferir de la pedida; se mantiene sincronizada.
            currency.value = response.data.currency
        } catch (err) {
            const data = err.response?.data

            error.value =
                Object.values(data?.errors ?? {})[0]?.[0] ??
                data?.message ??
                'No fue posible cargar el reporte.'
        } finally {
            loading.value = false
        }
    }

    function selectPreset(key) {
        preset.value = key

        if (key !== 'custom') {
            range.value = getPresetRange(key)

            return fetchReport()
        }
    }

    function setCustomRange(from, to) {
        range.value = { from, to }

        return fetchReport()
    }

    function selectCurrency(value) {
        currency.value = value

        return fetchReport()
    }

    return {
        preset,
        range,
        currency,
        report,
        loading,
        error,
        fetchReport,
        selectPreset,
        setCustomRange,
        selectCurrency,
    }
})
