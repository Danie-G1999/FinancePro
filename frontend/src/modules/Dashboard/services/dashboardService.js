import api from '../../../api/axios'

export const dashboardService = {
    async getSummary(filters = {}) {
        const response = await api.get('/dashboard', { params: filters })

        return response.data
    },
}
