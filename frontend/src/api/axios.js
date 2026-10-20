import axios from 'axios'

import { API_URL } from './config'

const api = axios.create({
    baseURL: `${API_URL}/api`,

    withCredentials: true,

    withXSRFToken: true,

    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
})

// Un 401 en cualquier petición (salvo las de autenticación) significa que la sesión
// expiró o se cerró: se avisa a la app para volver al login.
const AUTH_PATHS = ['/auth/me', '/auth/login', '/auth/register']

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const url = error.config?.url ?? ''

        if (error.response?.status === 401 && !AUTH_PATHS.some((path) => url.endsWith(path))) {
            window.dispatchEvent(new CustomEvent('auth:expired'))
        }

        return Promise.reject(error)
    }
)

export default api