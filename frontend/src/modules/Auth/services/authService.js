import api from '../../../api/axios'

const authService = {
    async csrf() {
        await api.get('/sanctum/csrf-cookie')
    },

    async login(credentials) {
        await this.csrf()

        const response = await api.post(
            '/api/auth/login',
            credentials
        )

        return response.data
    },

    async register(data) {
        await this.csrf()

        const response = await api.post(
            '/api/auth/register',
            data
        )

        return response.data
    },

    async me() {
        const response = await api.get('/api/auth/me')

        return response.data
    },

    async logout() {
        await this.csrf()

        const response = await api.post(
            '/api/auth/logout'
        )

        return response.data
    },
}

export default authService