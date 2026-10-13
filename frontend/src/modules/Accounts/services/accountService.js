import api from '../../../api/axios'

export const accountService = {
    async getAll() {
        const response = await api.get('/accounts')

        return response.data
    },

    async getById(id) {
        const response = await api.get(`/accounts/${id}`)

        return response.data
    },

    async create(data) {
        const response = await api.post('/accounts', data)

        return response.data
    },

    async update(id, data) {
        const response = await api.put(
            `/accounts/${id}`,
            data
        )

        return response.data
    },

    async delete(id) {
        const response = await api.delete(
            `/accounts/${id}`
        )

        return response.data
    },
}