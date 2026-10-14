import api from '../../../api/axios'

export const goalService = {
    async getAll() {
        const response = await api.get('/goals')

        return response.data
    },

    async create(data) {
        const response = await api.post('/goals', data)

        return response.data
    },

    async update(id, data) {
        const response = await api.put(`/goals/${id}`, data)

        return response.data
    },

    async delete(id) {
        const response = await api.delete(`/goals/${id}`)

        return response.data
    },
}
