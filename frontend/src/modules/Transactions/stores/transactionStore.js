import { defineStore } from 'pinia'
import axios from 'axios'
import { reactive, ref } from 'vue'

import { transactionService } from '../services/transactionService'

export const useTransactionStore = defineStore('transactions', () => {
    const transactions = ref([])

    const meta = ref({
        current_page: 1,
        last_page: 1,
        per_page: 20,
        total: 0,
        from: null,
        to: null,
    })

    const filters = reactive({
        search: '',
        date_from: '',
        date_to: '',
        account_id: '',
    })

    const loading = ref(false)

    const error = ref(null)

    function activeFilters() {
        return Object.fromEntries(
            Object.entries(filters).filter(([, value]) => value !== '')
        )
    }

    function hasFilters() {
        return Object.keys(activeFilters()).length > 0
    }

    // Cada búsqueda cancela la anterior: si no, una respuesta lenta de "Dom" podría
    // llegar después de la de "Domicilio" y dejar en la tabla resultados que no corresponden.
    let controller = null

    async function fetchTransactions(page = 1) {
        controller?.abort()

        const current = new AbortController()
        controller = current

        loading.value = true
        error.value = null

        try {
            const response = await transactionService.getAll(
                { ...activeFilters(), page },
                { signal: current.signal }
            )

            transactions.value = response.data
            meta.value = response.meta
        } catch (err) {
            if (axios.isCancel(err)) {
                return
            }

            const data = err.response?.data

            error.value =
                Object.values(data?.errors ?? {})[0]?.[0] ??
                data?.message ??
                'No fue posible cargar los movimientos.'
        } finally {
            // Solo la petición vigente apaga el indicador de carga.
            if (controller === current) {
                loading.value = false
            }
        }
    }

    function resetFilters() {
        Object.assign(filters, {
            search: '',
            date_from: '',
            date_to: '',
            account_id: '',
        })
    }

    return {
        transactions,
        meta,
        filters,
        loading,
        error,
        hasFilters,
        fetchTransactions,
        resetFilters,
    }
})
