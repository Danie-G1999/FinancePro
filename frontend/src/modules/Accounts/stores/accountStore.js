import { defineStore } from 'pinia'
import { ref } from 'vue'

import { accountService } from '../services/accountService'

export const useAccountStore = defineStore(
    'accounts',
    () => {
        const accounts = ref([])

        const loading = ref(false)

        const error = ref(null)

        // Indica si ya hubo una carga exitosa; tras recargar la página la lista arranca vacía.
        const loaded = ref(false)

        let pending = null

        function fetchAccounts() {
            pending = loadAccounts().finally(() => {
                pending = null
            })

            return pending
        }

        // Espera la carga en curso o la inicia si nunca se ha hecho.
        async function ensureLoaded() {
            if (pending) {
                return pending
            }

            if (!loaded.value) {
                return fetchAccounts()
            }
        }

        async function loadAccounts() {
            loading.value = true
            error.value = null

            try {
                const response =
                    await accountService.getAll()

                accounts.value = response.data
                loaded.value = true
            } catch (err) {
                console.error(
                    'Error loading accounts:',
                    err
                )

                error.value =
                    err.response?.data?.message ??
                    'No fue posible cargar las cuentas.'

                throw err
            } finally {
                loading.value = false
            }
        }

        async function createAccount(data) {
            loading.value = true
            error.value = null

            try {
                const response =
                    await accountService.create(data)

                const account = response.data

                accounts.value.push(account)

                return account
            } catch (err) {
                console.error(
                    'Error creating account:',
                    err
                )

                error.value =
                    err.response?.data?.message ??
                    'No fue posible crear la cuenta.'

                throw err
            } finally {
                loading.value = false
            }
        }

        async function updateAccount(id, data) {
            const response =
                await accountService.update(id, data)

            const index = accounts.value.findIndex(
                (account) => account.id === id
            )

            if (index !== -1) {
                accounts.value[index] = response.data
            }

            return response.data
        }

        async function deleteAccount(id) {
            await accountService.delete(id)

            accounts.value = accounts.value.filter(
                (account) => account.id !== id
            )
        }

        return {
            accounts,
            loading,
            error,
            loaded,
            fetchAccounts,
            ensureLoaded,
            createAccount,
            updateAccount,
            deleteAccount,
        }
    }
)