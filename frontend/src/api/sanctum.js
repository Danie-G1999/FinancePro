import axios from 'axios'

const sanctum = axios.create({
    baseURL: 'http://localhost:8001',

    withCredentials: true,

    withXSRFToken: true,

    headers: {
        Accept: 'application/json',
    },
})

export default sanctum