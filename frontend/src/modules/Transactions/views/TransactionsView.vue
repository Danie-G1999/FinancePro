<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

import TransactionDetailModal from '../components/TransactionDetailModal.vue'
import TransactionTable from '../../../components/common/TransactionTable.vue'
import { useTransactionStore } from '../stores/transactionStore'
import { useAccountStore } from '../../Accounts/stores/accountStore'
import { showError } from '../../../utils/alerts'

const FILTER_DEBOUNCE_MS = 350

const transactionStore = useTransactionStore()

const accountStore = useAccountStore()

const selectedTransaction = ref(null)

let debounceTimer = null

const invalidRange = computed(
    () =>
        transactionStore.filters.date_from !== '' &&
        transactionStore.filters.date_to !== '' &&
        transactionStore.filters.date_from > transactionStore.filters.date_to
)

function clearFilters() {
    transactionStore.resetFilters()
}

function goToPage(page) {
    transactionStore.fetchTransactions(page)
}

watch(
    () => ({ ...transactionStore.filters }),
    () => {
        clearTimeout(debounceTimer)

        if (invalidRange.value) {
            return
        }

        debounceTimer = setTimeout(
            () => transactionStore.fetchTransactions(1),
            FILTER_DEBOUNCE_MS
        )
    }
)

onMounted(() => {
    transactionStore.fetchTransactions(1)

    accountStore.fetchAccounts().catch(() => {
        showError('No fue posible cargar las cuentas.')
    })
})

onBeforeUnmount(() => clearTimeout(debounceTimer))
</script>

<template>
    <div class="transactions-page">

        <!-- HEADER -->

        <header class="page-header">

            <div>
                <h1 class="page-title">
                    Movimientos
                </h1>

                <p class="page-description">
                    Consulta todos tus ingresos y gastos en un solo lugar.
                </p>
            </div>

        </header>


        <!-- FILTERS -->

        <section class="filters-card">

            <div class="filter-group filter-search">
                <label for="filter-search" class="filter-label">
                    Buscar
                </label>

                <input
                    id="filter-search"
                    v-model="transactionStore.filters.search"
                    type="search"
                    class="filter-input"
                    placeholder="Buscar por descripción..."
                    maxlength="100"
                />
            </div>

            <div class="filter-group">
                <label for="filter-account" class="filter-label">
                    Cuenta
                </label>

                <select
                    id="filter-account"
                    v-model="transactionStore.filters.account_id"
                    class="filter-input"
                >
                    <option value="">
                        Todas las cuentas
                    </option>

                    <option
                        v-for="account in accountStore.accounts"
                        :key="account.id"
                        :value="account.id"
                    >
                        {{ account.name }}
                    </option>
                </select>
            </div>

            <div class="filter-group">
                <label for="filter-from" class="filter-label">
                    Desde
                </label>

                <input
                    id="filter-from"
                    v-model="transactionStore.filters.date_from"
                    type="date"
                    class="filter-input"
                    :max="transactionStore.filters.date_to || undefined"
                />
            </div>

            <div class="filter-group">
                <label for="filter-to" class="filter-label">
                    Hasta
                </label>

                <input
                    id="filter-to"
                    v-model="transactionStore.filters.date_to"
                    type="date"
                    class="filter-input"
                    :min="transactionStore.filters.date_from || undefined"
                />
            </div>

            <button
                v-if="transactionStore.hasFilters()"
                type="button"
                class="btn btn-secondary filter-clear"
                @click="clearFilters"
            >
                Limpiar
            </button>

            <span
                v-if="invalidRange"
                class="filter-error"
            >
                La fecha final no puede ser anterior a la inicial.
            </span>

        </section>


        <!-- LOADING -->

        <div
            v-if="transactionStore.loading && transactionStore.transactions.length === 0"
            class="state-container"
        >
            <div class="loading-spinner"></div>

            <p>
                Cargando movimientos...
            </p>
        </div>


        <!-- ERROR -->

        <div
            v-else-if="transactionStore.error"
            class="alert alert-error"
        >
            {{ transactionStore.error }}
        </div>


        <!-- EMPTY -->

        <section
            v-else-if="
                !transactionStore.loading &&
                transactionStore.transactions.length === 0
            "
            class="empty-state"
        >

            <div class="empty-icon">
                $
            </div>

            <template v-if="transactionStore.hasFilters()">
                <h2>
                    No se encontraron movimientos
                </h2>

                <p>
                    Ningún movimiento coincide con los filtros
                    seleccionados. Prueba con otros criterios.
                </p>

                <button
                    type="button"
                    class="btn btn-primary"
                    @click="clearFilters"
                >
                    Limpiar filtros
                </button>
            </template>

            <template v-else>
                <h2>
                    Aún no tienes movimientos
                </h2>

                <p>
                    Registra un ingreso o un gasto y aparecerá aquí.
                </p>
            </template>

        </section>


        <!-- TABLE -->

        <TransactionTable
            v-else
            :items="transactionStore.transactions"
            :meta="transactionStore.meta"
            :loading="transactionStore.loading"
            date-key="date"
            variant="mixed"
            item-label="movimientos"
            :actions="['view']"
            show-type
            @view="selectedTransaction = $event"
            @page-change="goToPage"
        />


        <!-- DETAIL -->

        <TransactionDetailModal
            v-if="selectedTransaction"
            :transaction="selectedTransaction"
            @close="selectedTransaction = null"
        />

    </div>
</template>

<style scoped src="../styles/transactions-view.css"></style>
