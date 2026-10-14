import api from '../../../api/axios'

export const recurringIncomeService = {
    async getAll() {
        const response = await api.get('/recurring-incomes')

        return response.data
    },

    async create(data) {
        const response = await api.post('/recurring-incomes', data)

        return response.data
    },

    async update(id, data) {
        const response = await api.put(`/recurring-incomes/${id}`, data)

        return response.data
    },

    async setActive(id, isActive) {
        const response = await api.patch(`/recurring-incomes/${id}/status`, {
            is_active: isActive,
        })

        return response.data
    },

    async delete(id) {
        const response = await api.delete(`/recurring-incomes/${id}`)

        return response.data
    },
}
