import api from '../../../api/axios'

export const budgetService = {
    async get(period) {
        const response = await api.get('/budgets', { params: { period } })

        return response.data
    },

    async save(data) {
        const response = await api.put('/budgets', data)

        return response.data
    },

    async reset(period) {
        const response = await api.delete('/budgets', { params: { period } })

        return response.data
    },
}
