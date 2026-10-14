<script setup>
import { computed } from 'vue'

import { formatMoney } from '../../../utils/money'

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },

    currency: {
        type: String,
        required: true,
    },
})

const RECOMMENDED_SAVINGS_RATE = 20

function money(value) {
    return formatMoney(value, props.currency)
}

// Para los ingresos, subir es bueno; para los gastos, subir es malo.
function changeLabel(change, higherIsBetter) {
    if (change === null) {
        return { text: 'Sin datos del período anterior', tone: 'neutral' }
    }

    if (change === 0) {
        return { text: 'Igual que el período anterior', tone: 'neutral' }
    }

    const improved = higherIsBetter ? change > 0 : change < 0

    return {
        text: `${change > 0 ? '▲' : '▼'} ${Math.abs(change)}% vs. período anterior`,
        tone: improved ? 'good' : 'bad',
    }
}

const incomeChange = computed(() =>
    changeLabel(props.summary.income_change_percent, true)
)

const expenseChange = computed(() =>
    changeLabel(props.summary.expenses_change_percent, false)
)

const savingsTone = computed(() => {
    if (props.summary.savings_rate === null) {
        return 'neutral'
    }

    return props.summary.savings_rate >= RECOMMENDED_SAVINGS_RATE ? 'good' : 'bad'
})
</script>

<template>
    <section class="kpis">

        <article class="kpi">
            <span class="kpi-label">Ingresos</span>

            <strong>{{ money(summary.income) }}</strong>

            <small :class="incomeChange.tone">
                {{ incomeChange.text }}
            </small>
        </article>

        <article class="kpi">
            <span class="kpi-label">Gastos</span>

            <strong>{{ money(summary.expenses) }}</strong>

            <small :class="expenseChange.tone">
                {{ expenseChange.text }}
            </small>
        </article>

        <article class="kpi">
            <span class="kpi-label">Balance del período</span>

            <strong :class="summary.balance < 0 ? 'bad' : 'good'">
                {{ money(summary.balance) }}
            </strong>

            <small class="neutral">
                Ingresos menos gastos
            </small>
        </article>

        <article class="kpi">
            <span class="kpi-label">Tasa de ahorro</span>

            <strong :class="savingsTone">
                {{ summary.savings_rate === null ? '—' : `${summary.savings_rate}%` }}
            </strong>

            <small class="neutral">
                Recomendado: {{ RECOMMENDED_SAVINGS_RATE }}% ·
                enviado a metas: {{ money(summary.saved_to_goals) }}
            </small>
        </article>

    </section>
</template>

<style scoped src="../styles/report-kpis.css"></style>
