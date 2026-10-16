import api from '../../../api/axios'

export const expenseCategoryService = {
    async getAll(filters = {}) {
        const response = await api.get('/expense-categories', {
            params: filters,
        })

        return response.data
    },

    async create(data) {
        const response = await api.post('/expense-categories', data)

        return response.data
    },

    async update(id, data) {
        const response = await api.put(`/expense-categories/${id}`, data)

        return response.data
    },

    async delete(id) {
        const response = await api.delete(`/expense-categories/${id}`)

        return response.data
    },
}
