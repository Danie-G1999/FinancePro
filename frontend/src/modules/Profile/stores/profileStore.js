import { defineStore } from 'pinia'
import { ref } from 'vue'

import { profileService } from '../services/profileService'

export const useProfileStore = defineStore('profile', () => {
    const profile = ref(null)

    const loading = ref(false)

    const error = ref(null)

    async function fetchProfile() {
        loading.value = true
        error.value = null

        try {
            const response = await profileService.get()

            profile.value = response.data
        } catch (err) {
            error.value =
                err.response?.data?.message ??
                'No fue posible cargar el perfil.'
        } finally {
            loading.value = false
        }
    }

    async function saveProfile(data) {
        const response = await profileService.update(data)

        profile.value = response.data

        return response.data
    }

    async function uploadPhoto(file) {
        const response = await profileService.uploadPhoto(file)

        profile.value = response.data

        return response.data
    }

    async function removePhoto() {
        const response = await profileService.removePhoto()

        profile.value = response.data

        return response.data
    }

    return {
        profile,
        loading,
        error,
        fetchProfile,
        saveProfile,
        uploadPhoto,
        removePhoto,
    }
})
