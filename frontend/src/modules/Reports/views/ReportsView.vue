<script setup>
import { computed, onMounted } from 'vue'

import ReportAccounts from '../components/ReportAccounts.vue'
import ReportCashFlow from '../components/ReportCashFlow.vue'
import ReportInsights from '../components/ReportInsights.vue'
import ReportKpis from '../components/ReportKpis.vue'
import ReportProgressPanel from '../components/ReportProgressPanel.vue'
import ReportSpending from '../components/ReportSpending.vue'
import ReportTopExpenses from '../components/ReportTopExpenses.vue'
import { useReportStore } from '../stores/reportStore'
import { REPORT_PRESETS } from '../utils/periods'
import { formatMoney } from '../../../utils/money'

const reportStore = useReportStore()

const report = computed(() => reportStore.report)

const isCustom = computed(() => reportStore.preset === 'custom')

const invalidRange = computed(
    () => reportStore.range.from > reportStore.range.to
)

const debtItems = computed(() =>
    report.value.debts.items.map((debt) => ({
        id: debt.id,
        name: debt.name,
        progress: debt.progress_percent,
        note: `${debt.paid_installments} de ${debt.installments} cuotas · pendiente ${money(debt.remaining)}`,
    }))
)

const goalItems = computed(() =>
    report.value.goals.items.map((goal) => ({
        id: goal.id,
        name: goal.name,
        progress: goal.progress_percent,
        note: goal.is_completed
            ? '¡Meta cumplida!'
            : `${money(goal.saved)} de ${money(goal.target)}`,
    }))
)

function money(value) {
    return formatMoney(value, report.value.currency)
}

function handleRangeChange(field, value) {
    const range = { ...reportStore.range, [field]: value }

    reportStore.range = range

    if (range.from && range.to && range.from <= range.to) {
        reportStore.setCustomRange(range.from, range.to)
    }
}

onMounted(() => {
    reportStore.fetchReport()
})
</script>

<template>
    <div class="reports-page">

        <!-- HEADER -->

        <header>
            <h1 class="page-title">
                Reportes
            </h1>

            <p class="page-description">
                Un análisis claro de tus cuentas, ingresos, gastos, deudas y metas.
            </p>
        </header>


        <!-- FILTERS -->

        <section class="filters-card">

            <div
                class="presets"
                role="group"
                aria-label="Período del reporte"
            >
                <button
                    v-for="preset in REPORT_PRESETS"
                    :key="preset.key"
                    type="button"
                    class="preset"
                    :class="{ active: reportStore.preset === preset.key }"
                    @click="reportStore.selectPreset(preset.key)"
                >
                    {{ preset.label }}
                </button>
            </div>

            <div
                v-if="isCustom"
                class="custom-range"
            >
                <label>
                    <span>Desde</span>

                    <input
                        type="date"
                        class="filter-input"
                        :value="reportStore.range.from"
                        :max="reportStore.range.to"
                        @change="handleRangeChange('from', $event.target.value)"
                    />
                </label>

                <label>
                    <span>Hasta</span>

                    <input
                        type="date"
                        class="filter-input"
                        :value="reportStore.range.to"
                        :min="reportStore.range.from"
                        @change="handleRangeChange('to', $event.target.value)"
                    />
                </label>
            </div>

            <label
                v-if="report && report.currencies.length > 1"
                class="currency-select"
            >
                <span>Moneda</span>

                <select
                    class="filter-input"
                    :value="reportStore.currency"
                    @change="reportStore.selectCurrency($event.target.value)"
                >
                    <option
                        v-for="code in report.currencies"
                        :key="code"
                        :value="code"
                    >
                        {{ code }}
                    </option>
                </select>
            </label>

            <span
                v-if="invalidRange"
                class="filter-error"
            >
                La fecha final no puede ser anterior a la inicial.
            </span>

        </section>


        <!-- LOADING / ERROR -->

        <div
            v-if="reportStore.loading && !report"
            class="state-container"
        >
            <div class="loading-spinner"></div>

            <p>Generando reporte...</p>
        </div>

        <div
            v-else-if="reportStore.error && !report"
            class="alert-error"
        >
            {{ reportStore.error }}
        </div>


        <!-- REPORT -->

        <div
            v-else-if="report"
            class="report"
            :class="{ refreshing: reportStore.loading }"
        >

            <p
                v-if="reportStore.error"
                class="alert-error"
            >
                {{ reportStore.error }}
            </p>

            <ReportInsights :insights="report.insights" />

            <ReportKpis
                :summary="report.summary"
                :currency="report.currency"
            />

            <div class="two-columns wide-left">
                <ReportCashFlow
                    :months="report.cash_flow"
                    :currency="report.currency"
                />

                <ReportSpending
                    :spending="report.spending"
                    :currency="report.currency"
                />
            </div>

            <ReportAccounts
                :accounts="report.accounts"
                :currency="report.currency"
            />

            <div class="two-columns">
                <ReportProgressPanel
                    title="Deudas"
                    subtitle="Cuánto llevas pagado y cuánto te falta."
                    done-label="Pagado"
                    :done="report.debts.paid"
                    :total="report.debts.total"
                    :progress="report.debts.progress_percent"
                    :items="debtItems"
                    :currency="report.currency"
                    empty-text="No tienes deudas registradas en esta moneda."
                />

                <ReportProgressPanel
                    title="Metas de ahorro"
                    subtitle="Tu avance hacia cada meta."
                    done-label="Ahorrado"
                    :done="report.goals.saved"
                    :total="report.goals.target"
                    :progress="report.goals.progress_percent"
                    :items="goalItems"
                    :currency="report.currency"
                    empty-text="No tienes metas de ahorro en esta moneda."
                />
            </div>

            <ReportTopExpenses
                :expenses="report.top_expenses"
                :currency="report.currency"
            />

        </div>

    </div>
</template>

<style scoped src="../styles/reports-view.css"></style>
