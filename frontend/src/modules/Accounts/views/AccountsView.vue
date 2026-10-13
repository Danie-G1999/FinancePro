<script setup>
import { onMounted, ref } from 'vue'

import AccountForm from '../components/AccountForm.vue'
import { useAccountStore } from '../stores/accountStore'
import {
    extractErrorMessage,
    showError,
    showSuccess,
    withLoading,
} from '../../../utils/alerts'

const accountStore = useAccountStore()

const showForm = ref(false)

const editingAccount = ref(null)

const accountToDelete = ref(null)

const deleting = ref(false)

function formatMoney(value, currency) {
    const amount = Number(value)

    try {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency,
            minimumFractionDigits: currency === 'COP' ? 0 : 2,
            maximumFractionDigits: 2,
        }).format(amount)
    } catch {
        return `${amount.toLocaleString('es-CO')} ${currency}`
    }
}

function openCreate() {
    editingAccount.value = null
    showForm.value = true
}

function openEdit(account) {
    editingAccount.value = account
    showForm.value = true
}

async function handleSaveAccount(data) {
    const isEditing = editingAccount.value !== null

    try {
        await withLoading(
            isEditing ? 'Guardando cambios...' : 'Guardando cuenta...',
            () =>
                isEditing
                    ? accountStore.updateAccount(
                          editingAccount.value.id,
                          data
                      )
                    : accountStore.createAccount(data)
        )

        showForm.value = false
        editingAccount.value = null

        showSuccess(
            isEditing
                ? 'Cuenta actualizada correctamente.'
                : 'Cuenta creada correctamente.'
        )
    } catch (error) {
        showError(
            extractErrorMessage(
                error,
                'No fue posible guardar la cuenta.'
            )
        )
    }
}

function handleCancel() {
    showForm.value = false
    editingAccount.value = null
}

function askDelete(account) {
    accountToDelete.value = account
}

async function confirmDelete() {
    deleting.value = true

    try {
        await withLoading('Eliminando cuenta...', () =>
            accountStore.deleteAccount(accountToDelete.value.id)
        )

        accountToDelete.value = null

        showSuccess('Cuenta eliminada correctamente.')
    } catch (error) {
        accountToDelete.value = null

        showError(
            extractErrorMessage(
                error,
                'No fue posible eliminar la cuenta.'
            )
        )
    } finally {
        deleting.value = false
    }
}

onMounted(() => {
    accountStore.fetchAccounts()
})
</script>

<template>
    <div class="accounts-page">

        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="page-header">

            <div>
                <h1 class="page-title">
                    Cuentas
                </h1>

                <p class="page-description">
                    Administra tus cuentas y saldos financieros.
                </p>
            </div>

            <button
                v-if="!showForm"
                type="button"
                class="btn btn-primary"
                @click="openCreate"
            >
                + Nueva cuenta
            </button>

        </header>


        <!-- =================================================
             CREATE FORM
        ================================================== -->

        <section
            v-if="showForm"
            class="form-card"
        >

            <div class="form-card-header">

                <div>
                    <h2>
                        {{ editingAccount ? 'Editar cuenta' : 'Nueva cuenta' }}
                    </h2>

                    <p>
                        {{
                            editingAccount
                                ? 'Modifica los datos de la cuenta.'
                                : 'Registra una nueva cuenta financiera.'
                        }}
                    </p>
                </div>

                <button
                    type="button"
                    class="close-button"
                    @click="handleCancel"
                >
                    ×
                </button>

            </div>

            <div class="form-card-body">

                <AccountForm
                    :key="editingAccount?.id ?? 'new'"
                    :account="editingAccount"
                    @submit="handleSaveAccount"
                    @cancel="handleCancel"
                />

            </div>

        </section>


        <!-- =================================================
             LOADING
        ================================================== -->

        <div
            v-if="
                accountStore.loading &&
                !showForm
            "
            class="state-container"
        >
            <div class="loading-spinner"></div>

            <p>
                Cargando cuentas...
            </p>
        </div>


        <!-- =================================================
             ERROR
        ================================================== -->

        <div
            v-else-if="
                accountStore.error &&
                !showForm
            "
            class="alert alert-error"
        >
            {{ accountStore.error }}
        </div>


        <!-- =================================================
             EMPTY STATE
        ================================================== -->

        <section
            v-else-if="
                !accountStore.loading &&
                accountStore.accounts.length === 0 &&
                !showForm
            "
            class="empty-state"
        >

            <div class="empty-icon">
                $
            </div>

            <h2>
                Aún no tienes cuentas
            </h2>

            <p>
                Registra tu primera cuenta para comenzar
                a controlar tus finanzas.
            </p>

            <button
                type="button"
                class="btn btn-primary"
                @click="openCreate"
            >
                Crear mi primera cuenta
            </button>

        </section>


        <!-- =================================================
             ACCOUNTS
        ================================================== -->

        <section
            v-else-if="
                accountStore.accounts.length > 0 &&
                !showForm
            "
            class="accounts-grid"
        >

            <article
                v-for="account in accountStore.accounts"
                :key="account.id"
                class="account-card"
            >

                <div class="account-card-header">

                    <div class="account-icon">
                        $
                    </div>

                    <div class="account-info">
                        <h3>
                            {{ account.name }}
                        </h3>

                        <span>
                            {{ account.type_label }}
                        </span>
                    </div>

                    <div class="account-actions">
                        <button
                            type="button"
                            class="icon-button"
                            title="Editar cuenta"
                            aria-label="Editar cuenta"
                            @click="openEdit(account)"
                        >
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9" />
                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                            </svg>
                        </button>

                        <button
                            type="button"
                            class="icon-button danger"
                            title="Eliminar cuenta"
                            aria-label="Eliminar cuenta"
                            @click="askDelete(account)"
                        >
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18" />
                                <path d="M8 6V4h8v2" />
                                <path d="M19 6l-1 14H6L5 6" />
                                <path d="M10 11v5M14 11v5" />
                            </svg>
                        </button>
                    </div>

                </div>


                <div class="account-card-body">

                    <span class="balance-label">
                        Saldo actual
                    </span>

                    <strong class="balance">
                        {{
                            formatMoney(
                                account.balance,
                                account.currency
                            )
                        }}
                    </strong>

                </div>


                <div class="account-card-footer">

                    <span
                        class="account-status"
                        :class="{
                            active: account.is_active,
                            inactive: !account.is_active,
                        }"
                    >
                        <span class="status-dot"></span>

                        {{
                            account.is_active
                                ? 'Activa'
                                : 'Inactiva'
                        }}
                    </span>

                    <span
                        v-if="account.institution"
                        class="institution"
                    >
                        {{ account.institution }}
                    </span>

                </div>

            </article>

        </section>


        <!-- =================================================
             DELETE CONFIRMATION
        ================================================== -->

        <div
            v-if="accountToDelete"
            class="modal-overlay"
            @click.self="!deleting && (accountToDelete = null)"
        >
            <div class="modal" role="dialog" aria-modal="true">
                <h2>
                    Eliminar cuenta
                </h2>

                <p>
                    ¿Seguro que deseas eliminar la cuenta
                    <strong>{{ accountToDelete.name }}</strong>?
                    Esta acción no se puede deshacer.
                </p>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        :disabled="deleting"
                        @click="accountToDelete = null"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger"
                        :disabled="deleting"
                        @click="confirmDelete"
                    >
                        {{ deleting ? 'Eliminando...' : 'Eliminar' }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>


<style scoped src="../styles/accounts-view.css"></style>