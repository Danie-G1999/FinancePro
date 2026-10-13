import api from '../../../api/axios'

export const dashboardService = {
    async getSummary() {
        const response = await api.get('/dashboard')

        return response.data
    },
}
