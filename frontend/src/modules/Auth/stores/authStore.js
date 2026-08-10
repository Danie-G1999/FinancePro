import { defineStore } from 'pinia'
import authService from '../services/authService'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        loading: false,
        initialized: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.user,
    },

    actions: {
        async login(credentials) {
            this.loading = true

            try {
                const user = await authService.login(credentials)

                this.user = user.data

                return this.user
            } finally {
                this.loading = false
            }
        },

        async register(data) {
            this.loading = true

            try {
                const user = await authService.register(data)

                this.user = user.data

                return this.user
            } finally {
                this.loading = false
            }
        },

        async fetchUser() {
            try {
                const response = await authService.me()

                this.user = response.data

                return this.user
            } catch (error) {
                this.user = null

                return null
            } finally {
                this.initialized = true
            }
        },

        async logout() {
            this.loading = true

            try {
                await authService.logout()
            } finally {
                this.user = null
                this.loading = false
            }
        },
    },
})