import api from '../../../api/axios'

export const transactionService = {
    async getAll(params = {}, { signal } = {}) {
        const response = await api.get('/transactions', { params, signal })

        return response.data
    },
}
