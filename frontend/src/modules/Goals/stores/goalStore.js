import { defineStore } from 'pinia'
import { ref } from 'vue'

import { goalService } from '../services/goalService'

export const useGoalStore = defineStore('goals', () => {
    const goals = ref([])

    const loading = ref(false)

    const error = ref(null)

    async function fetchGoals() {
        loading.value = true
        error.value = null

        try {
            const response = await goalService.getAll()

            goals.value = response.data
        } catch (err) {
            error.value =
                err.response?.data?.message ??
                'No fue posible cargar las metas.'
        } finally {
            loading.value = false
        }
    }

    async function createGoal(data) {
        const response = await goalService.create(data)

        await fetchGoals()

        return response.data
    }

    async function updateGoal(id, data) {
        const response = await goalService.update(id, data)

        await fetchGoals()

        return response.data
    }

    async function deleteGoal(id) {
        await goalService.delete(id)

        goals.value = goals.value.filter((goal) => goal.id !== id)
    }

    return {
        goals,
        loading,
        error,
        fetchGoals,
        createGoal,
        updateGoal,
        deleteGoal,
    }
})
