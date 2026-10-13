import api from '../../../api/axios'

export const incomeService = {
    async getAll(page = 1) {
        const response = await api.get('/incomes', {
            params: { page },
        })

        return response.data
    },

    async create(data) {
        const response = await api.post('/incomes', data)

        return response.data
    },

    async update(id, data) {
        const response = await api.put(`/incomes/${id}`, data)

        return response.data
    },

    async delete(id) {
        const response = await api.delete(`/incomes/${id}`)

        return response.data
    },
}
