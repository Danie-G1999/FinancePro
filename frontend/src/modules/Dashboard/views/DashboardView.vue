<script setup>
import { computed, onMounted } from 'vue'
import { useCurrencyFormatter } from '../../../composables/useCurrencyFormatter'

import { useAuthStore } from '../../Auth/stores/authStore'
import { useDashboardStore } from '../stores/dashboardStore'
import { formatMoney } from '../../../utils/money'

const authStore = useAuthStore()

const dashboardStore = useDashboardStore()

const formattedDate = computed(() => {
    return new Intl.DateTimeFormat('es-CO', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date())
})

// El backend ya elige la moneda preferida; mientras carga se usa la del usuario.
const { preferredCurrency } = useCurrencyFormatter()

const currency = computed(() => dashboardStore.data?.currency ?? preferredCurrency.value)

const summary = computed(() => dashboardStore.data?.summary ?? null)

const cashFlow = computed(() => dashboardStore.data?.cash_flow ?? [])

const movements = computed(
    () => dashboardStore.data?.recent_movements ?? []
)

const chartMax = computed(() =>
    Math.max(
        0,
        ...cashFlow.value.flatMap((month) => [month.income, month.expense])
    )
)

const hasChartData = computed(() => chartMax.value > 0)

function barHeight(value) {
    if (chartMax.value === 0 || value === 0) {
        return '0%'
    }

    // Altura mínima para que los valores pequeños sigan siendo visibles.
    return `${Math.max((value / chartMax.value) * 100, 3)}%`
}

function money(value, code = currency.value) {
    return formatMoney(value, code)
}

function formatDate(value) {
    const [year, month, day] = value.split('-')

    return `${day}/${month}/${year}`
}

const accounts = computed(() => dashboardStore.data?.accounts ?? [])

const isCurrentMonth = computed(
    () => dashboardStore.period === dashboardStore.currentPeriod()
)

const isFiltered = computed(
    () => !isCurrentMonth.value || dashboardStore.accountId !== null
)

const monthLabel = computed(() => {
    const [year, month] = dashboardStore.period.split('-').map(Number)

    const label = new Intl.DateTimeFormat('es-CO', {
        month: 'long',
        year: 'numeric',
    }).format(new Date(year, month - 1, 1))

    return label.charAt(0).toUpperCase() + label.slice(1)
})

const periodText = computed(() =>
    isCurrentMonth.value ? 'Este mes' : monthLabel.value
)

const selectedAccountName = computed(
    () => accounts.value.find((account) => account.id === dashboardStore.accountId)?.name ?? null
)

function shiftMonth(delta) {
    const [year, month] = dashboardStore.period.split('-').map(Number)

    const date = new Date(year, month - 1 + delta, 1)

    const period = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`

    dashboardStore.setFilters({ period })
}

function handlePeriodInput(event) {
    if (event.target.value) {
        dashboardStore.setFilters({ period: event.target.value })
    }
}

function handleAccountChange(event) {
    const value = event.target.value

    dashboardStore.setFilters({ accountId: value ? Number(value) : null })
}

onMounted(() => {
    dashboardStore.fetchSummary()
})
</script>

<template>
    <section class="dashboard">
        <div class="welcome">
            <div>
                <h2>
                    Hola, {{ authStore.user?.name ?? 'Juan' }} 👋
                </h2>

                <p>
                    Este es el resumen de tus finanzas personales.
                </p>
            </div>

            <span>
                {{ formattedDate }}
            </span>
        </div>

        <div
            v-if="dashboardStore.data"
            class="filters"
        >
            <div class="period-nav">
                <button
                    type="button"
                    class="icon-nav"
                    aria-label="Mes anterior"
                    :disabled="dashboardStore.loading"
                    @click="shiftMonth(-1)"
                >
                    ‹
                </button>

                <label class="period-label">
                    <span>{{ monthLabel }}</span>

                    <input
                        type="month"
                        :value="dashboardStore.period"
                        :max="dashboardStore.currentPeriod()"
                        aria-label="Seleccionar mes"
                        @change="handlePeriodInput"
                    />
                </label>

                <button
                    type="button"
                    class="icon-nav"
                    aria-label="Mes siguiente"
                    :disabled="dashboardStore.loading || isCurrentMonth"
                    @click="shiftMonth(1)"
                >
                    ›
                </button>
            </div>

            <select
                class="account-filter"
                aria-label="Filtrar por cuenta"
                :value="dashboardStore.accountId ?? ''"
                :disabled="dashboardStore.loading"
                @change="handleAccountChange"
            >
                <option value="">
                    Todas las cuentas
                </option>

                <option
                    v-for="account in accounts"
                    :key="account.id"
                    :value="account.id"
                >
                    {{ account.name }} ({{ account.currency }})
                </option>
            </select>

            <button
                v-if="isFiltered"
                type="button"
                class="clear-filters"
                :disabled="dashboardStore.loading"
                @click="dashboardStore.resetFilters()"
            >
                Limpiar filtros
            </button>
        </div>

        <div
            v-if="dashboardStore.loading && !dashboardStore.data"
            class="state-container"
        >
            <div class="loading-spinner"></div>

            <p>Cargando resumen...</p>
        </div>

        <div
            v-else-if="dashboardStore.error && !dashboardStore.data"
            class="state-error"
        >
            {{ dashboardStore.error }}
        </div>

        <template v-else-if="summary">
            <div class="summary-grid">
                <article class="summary-card">
                    <div class="card-label">
                        <span>Saldo total</span>

                        <div class="card-icon">
                            $
                        </div>
                    </div>

                    <strong>{{ money(summary.total_balance) }}</strong>

                    <small v-if="selectedAccountName">
                        Saldo actual de {{ selectedAccountName }}
                    </small>

                    <small v-else>
                        {{ summary.accounts_count }}
                        {{ summary.accounts_count === 1 ? 'cuenta activa' : 'cuentas activas' }}
                    </small>
                </article>

                <article class="summary-card">
                    <div class="card-label">
                        <span>Ingresos</span>

                        <div class="card-icon income">
                            ↑
                        </div>
                    </div>

                    <strong>{{ money(summary.month_income) }}</strong>

                    <small>
                        {{ periodText }}
                    </small>
                </article>

                <article class="summary-card">
                    <div class="card-label">
                        <span>Gastos</span>

                        <div class="card-icon expense">
                            ↓
                        </div>
                    </div>

                    <strong>{{ money(summary.month_expense) }}</strong>

                    <small>
                        {{ periodText }}
                    </small>
                </article>

                <article class="summary-card">
                    <div class="card-label">
                        <span>Deudas</span>

                        <div class="card-icon">
                            #
                        </div>
                    </div>

                    <strong>{{ money(summary.pending_debt) }}</strong>

                    <small>
                        Pendiente por pagar
                    </small>
                </article>
            </div>

            <div class="dashboard-grid">
                <article class="panel">
                    <div class="panel-header">
                        <div>
                            <h3>Flujo de dinero</h3>
                            <span>
                                Ingresos vs. gastos ·
                                {{ isCurrentMonth ? 'últimos 6 meses' : `6 meses hasta ${monthLabel}` }}
                            </span>
                        </div>

                        <div class="legend">
                            <span class="legend-item">
                                <i class="dot income"></i>
                                Ingresos
                            </span>

                            <span class="legend-item">
                                <i class="dot expense"></i>
                                Gastos
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="hasChartData"
                        class="chart"
                    >
                        <div
                            v-for="month in cashFlow"
                            :key="month.month"
                            class="chart-column"
                        >
                            <div class="bars">
                                <div
                                    class="bar income"
                                    :style="{ height: barHeight(month.income) }"
                                    :title="`Ingresos: ${money(month.income)}`"
                                ></div>

                                <div
                                    class="bar expense"
                                    :style="{ height: barHeight(month.expense) }"
                                    :title="`Gastos: ${money(month.expense)}`"
                                ></div>
                            </div>

                            <span class="chart-label">
                                {{ month.label }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-else
                        class="empty-chart"
                    >
                        <div class="empty-icon">
                            ↗
                        </div>

                        <strong>
                            Aún no hay movimientos
                        </strong>

                        <span>
                            Cuando registres ingresos y gastos,
                            aparecerán aquí.
                        </span>
                    </div>
                </article>

                <article class="panel">
                    <div class="panel-header">
                        <div>
                            <h3>Resumen financiero</h3>
                            <span>{{ periodText }}</span>
                        </div>
                    </div>

                    <div class="financial-summary">
                        <div>
                            <span>Ingresos</span>
                            <strong class="positive">
                                {{ money(summary.month_income) }}
                            </strong>
                        </div>

                        <div>
                            <span>Gastos</span>
                            <strong class="negative">
                                {{ money(summary.month_expense) }}
                            </strong>
                        </div>

                        <div>
                            <span>Balance</span>
                            <strong
                                :class="
                                    summary.month_balance < 0
                                        ? 'negative'
                                        : 'positive'
                                "
                            >
                                {{ money(summary.month_balance) }}
                            </strong>
                        </div>
                    </div>
                </article>
            </div>

            <article class="panel transactions-panel">
                <div class="panel-header">
                    <div>
                        <h3>Últimos movimientos</h3>
                        <span>
                            {{ isCurrentMonth ? 'Actividad reciente' : `Actividad de ${monthLabel}` }}
                            <template v-if="selectedAccountName">
                                · {{ selectedAccountName }}
                            </template>
                        </span>
                    </div>

                    <div class="panel-actions">
                        <router-link
                            :to="{ name: 'ingresos' }"
                            class="panel-link"
                        >
                            Ingresos
                        </router-link>

                        <router-link
                            :to="{ name: 'gastos' }"
                            class="panel-link"
                        >
                            Gastos
                        </router-link>
                    </div>
                </div>

                <ul
                    v-if="movements.length > 0"
                    class="movements"
                >
                    <li
                        v-for="movement in movements"
                        :key="`${movement.type}-${movement.id}`"
                        class="movement"
                    >
                        <div
                            class="movement-icon"
                            :class="movement.type"
                        >
                            {{ movement.type === 'income' ? '↑' : '↓' }}
                        </div>

                        <div class="movement-info">
                            <strong>{{ movement.description }}</strong>

                            <span>
                                {{ movement.account_name }}
                                · {{ formatDate(movement.date) }}
                            </span>
                        </div>

                        <strong
                            class="movement-amount"
                            :class="movement.type"
                        >
                            {{ movement.type === 'income' ? '+' : '-' }}{{
                                money(movement.amount, movement.currency)
                            }}
                        </strong>
                    </li>
                </ul>

                <div
                    v-else
                    class="empty-transactions"
                >
                    <div class="empty-icon">
                        +
                    </div>

                    <strong>
                        {{ isFiltered ? 'Sin movimientos con estos filtros' : 'No hay movimientos registrados' }}
                    </strong>

                    <span>
                        {{
                            isFiltered
                                ? 'Prueba con otro mes u otra cuenta.'
                                : 'Empieza agregando tu primer ingreso o gasto.'
                        }}
                    </span>
                </div>
            </article>
        </template>
    </section>
</template>

<style scoped>
.dashboard {
    width: 100%;
}

.welcome {
    margin-bottom: 24px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
}

.welcome h2 {
    margin: 0;
    color: #111827;
    font-size: 22px;
    font-weight: 700;
}

.welcome p {
    margin: 6px 0 0;
    color: #6b7280;
    font-size: 13px;
}

.welcome > span {
    color: #9ca3af;
    font-size: 11px;
}

.filters {
    margin-bottom: 16px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}

.period-nav {
    display: flex;
    align-items: center;
    gap: 6px;
}

.icon-nav {
    width: 36px;
    height: 36px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #ffffff;
    color: #6b7280;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
}

.icon-nav:hover:not(:disabled) {
    background: #f9fafb;
    color: #2563eb;
}

.icon-nav:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.period-label {
    position: relative;
    min-width: 170px;
    height: 36px;
    padding: 0 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #ffffff;
    color: #111827;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.period-label input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.account-filter {
    height: 36px;
    min-width: 200px;
    padding: 0 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #ffffff;
    color: #111827;
    font-size: 13px;
    cursor: pointer;
}

.account-filter:focus {
    outline: 2px solid #bfdbfe;
    border-color: #2563eb;
}

.clear-filters {
    height: 36px;
    padding: 0 12px;
    border: 0;
    background: transparent;
    color: #2563eb;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.clear-filters:hover:not(:disabled) {
    text-decoration: underline;
}

.state-container {
    min-height: 250px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: #6b7280;
    font-size: 13px;
}

.loading-spinner {
    width: 28px;
    height: 28px;
    border: 3px solid #e5e7eb;
    border-top-color: #2563eb;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.state-error {
    padding: 12px 14px;
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: #fef2f2;
    color: #dc2626;
    font-size: 13px;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.summary-card {
    min-width: 0;
    padding: 20px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
}

.card-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card-label > span {
    color: #6b7280;
    font-size: 12px;
}

.card-icon {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 14px;
    font-weight: 700;
}

.card-icon.income {
    background: #f0fdf4;
    color: #16a34a;
}

.card-icon.expense {
    background: #fef2f2;
    color: #dc2626;
}

.summary-card > strong {
    display: block;
    margin-top: 17px;
    overflow: hidden;
    color: #111827;
    font-size: 24px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.summary-card small {
    display: block;
    margin-top: 6px;
    color: #9ca3af;
    font-size: 11px;
}

.dashboard-grid {
    margin-top: 16px;
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 16px;
}

.panel {
    min-width: 0;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
}

.panel-header {
    padding: 19px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid #f0f1f3;
}

.panel-header h3 {
    margin: 0;
    color: #111827;
    font-size: 14px;
    font-weight: 650;
}

.panel-header span {
    display: block;
    margin-top: 4px;
    color: #9ca3af;
    font-size: 11px;
}

.panel-actions {
    display: flex;
    gap: 8px;
}

.panel-link {
    padding: 7px 11px;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    background: #ffffff;
    color: #6b7280;
    font-size: 11px;
    text-decoration: none;
}

.panel-link:hover {
    background: #f9fafb;
    color: #2563eb;
}

.legend {
    display: flex;
    gap: 14px;
}

.legend-item {
    display: flex !important;
    align-items: center;
    gap: 6px;
    margin-top: 0 !important;
}

.dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.dot.income,
.bar.income {
    background: #16a34a;
}

.dot.expense,
.bar.expense {
    background: #dc2626;
}

.chart {
    height: 260px;
    padding: 20px;
    display: flex;
    align-items: stretch;
    gap: 12px;
}

.chart-column {
    min-width: 0;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.bars {
    flex: 1;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 4px;
    border-bottom: 1px solid #e5e7eb;
}

.bar {
    width: 100%;
    max-width: 28px;
    border-radius: 4px 4px 0 0;
    transition: height 0.3s ease;
}

.chart-label {
    color: #9ca3af;
    font-size: 11px;
    text-align: center;
}

.empty-chart {
    min-height: 260px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.empty-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #f3f4f6;
    color: #9ca3af;
    font-size: 18px;
}

.empty-chart strong,
.empty-transactions strong {
    margin-top: 12px;
    color: #374151;
    font-size: 12px;
}

.empty-chart span,
.empty-transactions span {
    max-width: 280px;
    margin-top: 5px;
    color: #9ca3af;
    font-size: 11px;
    line-height: 1.5;
}

.financial-summary {
    padding: 10px 20px 20px;
}

.financial-summary > div {
    padding: 15px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid #f3f4f6;
}

.financial-summary > div:last-child {
    border-bottom: 0;
}

.financial-summary span {
    color: #6b7280;
    font-size: 12px;
}

.financial-summary strong {
    color: #111827;
    font-size: 14px;
}

.positive {
    color: #16a34a !important;
}

.negative {
    color: #dc2626 !important;
}

.transactions-panel {
    margin-top: 16px;
}

.movements {
    margin: 0;
    padding: 0 20px;
    list-style: none;
}

.movement {
    padding: 14px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid #f3f4f6;
}

.movement:last-child {
    border-bottom: 0;
}

.movement-icon {
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    font-size: 14px;
    font-weight: 700;
}

.movement-icon.income {
    background: #f0fdf4;
    color: #16a34a;
}

.movement-icon.expense {
    background: #fef2f2;
    color: #dc2626;
}

.movement-info {
    min-width: 0;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.movement-info strong {
    overflow: hidden;
    color: #111827;
    font-size: 13px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.movement-info span {
    color: #9ca3af;
    font-size: 11px;
}

.movement-amount {
    flex-shrink: 0;
    font-size: 13px;
}

.movement-amount.income {
    color: #16a34a;
}

.movement-amount.expense {
    color: #dc2626;
}

.empty-transactions {
    min-height: 180px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

@media (max-width: 1100px) {
    .summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .summary-grid {
        grid-template-columns: 1fr;
    }

    .welcome {
        align-items: flex-start;
        flex-direction: column;
    }

    .period-nav,
    .account-filter {
        width: 100%;
    }

    .period-label {
        flex: 1;
    }

    .panel-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .chart {
        padding: 16px 12px;
        gap: 6px;
    }
}
</style>
