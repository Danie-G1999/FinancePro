import api from '../../../api/axios'
import csrf from '../../../api/sanctum'

export const authService = {
    async getCsrfCookie() {
        return await csrf.get('/sanctum/csrf-cookie')
    },

    async login(credentials) {
        await this.getCsrfCookie()

        return await api.post(
            '/auth/login',
            credentials
        )
    },

    async logout() {
        return await api.post('/auth/logout')
    },

    async me() {
        return await api.get('/auth/me')
    },

    async register(data) {
        await this.getCsrfCookie()

        return await api.post(
            '/auth/register',
            data
        )
    },
}