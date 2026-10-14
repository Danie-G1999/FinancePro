import api from '../../../api/axios'

export const reportService = {
    async get(params) {
        const response = await api.get('/reports', { params })

        return response.data
    },
}
