import api from '../../../api/axios'

export const debtService = {
    async getAll() {
        const response = await api.get('/debts')

        return response.data
    },

    async create(data) {
        const response = await api.post('/debts', data)

        return response.data
    },

    async update(id, data) {
        const response = await api.put(`/debts/${id}`, data)

        return response.data
    },

    async delete(id) {
        const response = await api.delete(`/debts/${id}`)

        return response.data
    },
}
