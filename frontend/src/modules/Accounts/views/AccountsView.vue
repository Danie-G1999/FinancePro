<script setup>
import { onMounted, ref } from 'vue'

import AccountForm from '../components/AccountForm.vue'
import { useAccountStore } from '../stores/accountStore'

const accountStore = useAccountStore()

const showForm = ref(false)

const successMessage = ref('')

async function handleCreateAccount(data) {
    successMessage.value = ''

    try {
        await accountStore.createAccount(data)

        showForm.value = false

        successMessage.value =
            'Cuenta creada correctamente.'

        setTimeout(() => {
            successMessage.value = ''
        }, 4000)
    } catch (error) {
        console.error(
            'No fue posible crear la cuenta:',
            error
        )
    }
}

function handleCancel() {
    showForm.value = false
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
                @click="showForm = true"
            >
                + Nueva cuenta
            </button>

        </header>


        <!-- =================================================
             SUCCESS MESSAGE
        ================================================== -->

        <div
            v-if="successMessage"
            class="alert alert-success"
        >
            {{ successMessage }}
        </div>


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
                        Nueva cuenta
                    </h2>

                    <p>
                        Registra una nueva cuenta financiera.
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
                    @submit="handleCreateAccount"
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
                @click="showForm = true"
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

                    <div>
                        <h3>
                            {{ account.name }}
                        </h3>

                        <span>
                            {{ account.type_label }}
                        </span>
                    </div>

                </div>


                <div class="account-card-body">

                    <span class="balance-label">
                        Saldo inicial
                    </span>

                    <strong class="balance">
                        {{ account.initial_balance }}
                        {{ account.currency }}
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

    </div>
</template>


<style scoped>
.accounts-page {
    display: flex;
    flex-direction: column;

    gap: 24px;

    width: 100%;
}


/* =========================================================
   HEADER
   ========================================================= */

.page-header {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 20px;
}

.page-title {
    font-size: 24px;
    font-weight: 700;

    color: var(--color-text-primary);
}

.page-description {
    margin-top: 5px;

    color: var(--color-text-muted);

    font-size: 13px;
}


/* =========================================================
   BUTTONS
   ========================================================= */

.btn {
    min-height: 40px;

    padding: 0 16px;

    border: 1px solid transparent;
    border-radius: var(--radius-md);

    font-size: 13px;
    font-weight: 600;

    transition:
        background var(--transition-fast),
        border-color var(--transition-fast),
        box-shadow var(--transition-fast);
}

.btn-primary {
    background: var(--color-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--color-primary-hover);

    box-shadow: var(--shadow-sm);
}


/* =========================================================
   SUCCESS
   ========================================================= */

.alert {
    padding: 12px 14px;

    border-radius: var(--radius-md);

    font-size: 13px;
}

.alert-success {
    background: var(--color-success-light);
    color: var(--color-success);

    border: 1px solid #bbf7d0;
}

.alert-error {
    background: var(--color-danger-light);
    color: var(--color-danger);

    border: 1px solid #fecaca;
}


/* =========================================================
   FORM CARD
   ========================================================= */

.form-card {
    background: var(--color-surface);

    border: 1px solid var(--color-border);

    border-radius: var(--radius-lg);

    box-shadow: var(--shadow-sm);

    overflow: hidden;
}

.form-card-header {
    display: flex;

    align-items: center;
    justify-content: space-between;

    padding: 20px 24px;

    border-bottom: 1px solid var(--color-border-light);
}

.form-card-header h2 {
    font-size: 16px;
    font-weight: 600;
}

.form-card-header p {
    margin-top: 4px;

    color: var(--color-text-muted);

    font-size: 12px;
}

.form-card-body {
    padding: 24px;
}

.close-button {
    width: 32px;
    height: 32px;

    border: 0;
    border-radius: var(--radius-md);

    background: transparent;

    color: var(--color-text-muted);

    font-size: 24px;
    line-height: 1;

    cursor: pointer;

    transition:
        background var(--transition-fast),
        color var(--transition-fast);
}

.close-button:hover {
    background: var(--color-surface-hover);
    color: var(--color-text-primary);
}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.empty-state {
    display: flex;

    flex-direction: column;

    align-items: center;
    justify-content: center;

    text-align: center;

    min-height: 360px;

    padding: 40px;

    background: var(--color-surface);

    border: 1px dashed var(--color-border-dark);

    border-radius: var(--radius-lg);
}

.empty-icon {
    display: flex;

    align-items: center;
    justify-content: center;

    width: 64px;
    height: 64px;

    margin-bottom: 16px;

    border-radius: 50%;

    background: var(--color-primary-light);

    color: var(--color-primary);

    font-size: 24px;
    font-weight: 700;
}

.empty-state h2 {
    font-size: 17px;
    font-weight: 600;
}

.empty-state p {
    max-width: 400px;

    margin-top: 8px;
    margin-bottom: 20px;

    color: var(--color-text-muted);

    font-size: 13px;
    line-height: 1.6;
}


/* =========================================================
   LOADING
   ========================================================= */

.state-container {
    display: flex;

    flex-direction: column;

    align-items: center;
    justify-content: center;

    min-height: 250px;

    gap: 12px;

    color: var(--color-text-muted);

    font-size: 13px;
}

.loading-spinner {
    width: 28px;
    height: 28px;

    border: 3px solid #e5e7eb;
    border-top-color: var(--color-primary);

    border-radius: 50%;

    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}


/* =========================================================
   ACCOUNTS GRID
   ========================================================= */

.accounts-grid {
    display: grid;

    grid-template-columns:
        repeat(
            auto-fill,
            minmax(280px, 1fr)
        );

    gap: 16px;
}


/* =========================================================
   ACCOUNT CARD
   ========================================================= */

.account-card {
    display: flex;

    flex-direction: column;

    min-height: 190px;

    background: var(--color-surface);

    border: 1px solid var(--color-border);

    border-radius: var(--radius-lg);

    box-shadow: var(--shadow-sm);

    overflow: hidden;

    transition:
        box-shadow var(--transition-normal),
        transform var(--transition-normal);
}

.account-card:hover {
    box-shadow: var(--shadow-md);

    transform: translateY(-1px);
}

.account-card-header {
    display: flex;

    align-items: center;

    gap: 12px;

    padding: 18px;

    border-bottom: 1px solid var(--color-border-light);
}

.account-icon {
    display: flex;

    align-items: center;
    justify-content: center;

    width: 42px;
    height: 42px;

    flex-shrink: 0;

    border-radius: var(--radius-md);

    background: var(--color-primary-light);

    color: var(--color-primary);

    font-size: 17px;
    font-weight: 700;
}

.account-card-header h3 {
    font-size: 14px;
    font-weight: 600;
}

.account-card-header span {
    display: block;

    margin-top: 3px;

    color: var(--color-text-muted);

    font-size: 11px;
}

.account-card-body {
    display: flex;

    flex-direction: column;

    gap: 5px;

    padding: 18px;
}

.balance-label {
    color: var(--color-text-muted);

    font-size: 11px;
}

.balance {
    color: var(--color-text-primary);

    font-size: 22px;
    font-weight: 700;

    letter-spacing: -0.5px;
}

.account-card-footer {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 10px;

    margin-top: auto;

    padding: 12px 18px;

    border-top: 1px solid var(--color-border-light);
}

.account-status {
    display: inline-flex;

    align-items: center;

    gap: 6px;

    font-size: 11px;
    font-weight: 600;
}

.status-dot {
    width: 7px;
    height: 7px;

    border-radius: 50%;
}

.account-status.active {
    color: var(--color-success);
}

.account-status.active .status-dot {
    background: var(--color-success);
}

.account-status.inactive {
    color: var(--color-text-muted);
}

.account-status.inactive .status-dot {
    background: var(--color-text-light);
}

.institution {
    max-width: 150px;

    overflow: hidden;

    color: var(--color-text-muted);

    font-size: 11px;

    text-overflow: ellipsis;

    white-space: nowrap;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 640px) {
    .page-header {
        align-items: flex-start;

        flex-direction: column;
    }

    .page-header .btn {
        width: 100%;
    }

    .form-card-body {
        padding: 18px;
    }

    .accounts-grid {
        grid-template-columns: 1fr;
    }
}
</style>