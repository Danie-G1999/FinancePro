import axios from 'axios'

import { API_URL } from './config'

const sanctum = axios.create({
    baseURL: API_URL,

    withCredentials: true,

    withXSRFToken: true,

    headers: {
        Accept: 'application/json',
    },
})

export default sanctum