import axios from 'axios'
import api from '../../../api/axios'

const csrf = axios.create({
    baseURL: 'http://localhost:8001',

    withCredentials: true,

    withXSRFToken: true,

    headers: {
        Accept: 'application/json',
    },
})

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