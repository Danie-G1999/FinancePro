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

export default api