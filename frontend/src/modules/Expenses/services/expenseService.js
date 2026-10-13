import api from '../../../api/axios'

export const expenseService = {
    async getAll(page = 1) {
        const response = await api.get('/expenses', {
            params: { page },
        })

        return response.data
    },

    async create(data) {
        const response = await api.post('/expenses', data)

        return response.data
    },

    async update(id, data) {
        const response = await api.put(`/expenses/${id}`, data)

        return response.data
    },

    async delete(id) {
        const response = await api.delete(`/expenses/${id}`)

        return response.data
    },
}
