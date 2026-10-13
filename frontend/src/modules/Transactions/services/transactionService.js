import api from '../../../api/axios'

export const transactionService = {
    async getAll(params = {}) {
        const response = await api.get('/transactions', { params })

        return response.data
    },
}
