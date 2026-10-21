import api from '../../../api/axios'

export const profileService = {
    async get() {
        const response = await api.get('/profile')

        return response.data
    },

    async update(data) {
        const response = await api.put('/profile', data)

        return response.data
    },

    async uploadPhoto(file) {
        const formData = new FormData()

        formData.append('photo', file)

        const response = await api.post('/profile/photo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })

        return response.data
    },

    // Si la cuenta no tiene contraseña (creada con Google) no se envía current_password.
    async updatePassword(data) {
        const response = await api.put('/profile/password', data)

        return response.data
    },

    async removePhoto() {
        const response = await api.delete('/profile/photo')

        return response.data
    },
}
