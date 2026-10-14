import { defineStore } from 'pinia'
import { ref } from 'vue'

import { recurringIncomeService } from '../services/recurringIncomeService'

export const useRecurringIncomeStore = defineStore('recurringIncomes', () => {
    const schedules = ref([])

    const loading = ref(false)

    const error = ref(null)

    async function fetchSchedules() {
        loading.value = true
        error.value = null

        try {
            const response = await recurringIncomeService.getAll()

            schedules.value = response.data
        } catch (err) {
            error.value =
                err.response?.data?.message ??
                'No fue posible cargar los ingresos recurrentes.'
        } finally {
            loading.value = false
        }
    }

    async function createSchedule(data) {
        await recurringIncomeService.create(data)

        await fetchSchedules()
    }

    async function updateSchedule(id, data) {
        await recurringIncomeService.update(id, data)

        await fetchSchedules()
    }

    async function setActive(id, isActive) {
        await recurringIncomeService.setActive(id, isActive)

        await fetchSchedules()
    }

    async function deleteSchedule(id) {
        await recurringIncomeService.delete(id)

        schedules.value = schedules.value.filter((schedule) => schedule.id !== id)
    }

    return {
        schedules,
        loading,
        error,
        fetchSchedules,
        createSchedule,
        updateSchedule,
        setActive,
        deleteSchedule,
    }
})
