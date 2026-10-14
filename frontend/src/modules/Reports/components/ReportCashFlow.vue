<script setup>
import { computed } from 'vue'

import { formatMoney } from '../../../utils/money'

const props = defineProps({
    months: {
        type: Array,
        required: true,
    },

    currency: {
        type: String,
        required: true,
    },
})

const chartMax = computed(() =>
    Math.max(0, ...props.months.flatMap((month) => [month.income, month.expense]))
)

const hasData = computed(() => chartMax.value > 0)

function barHeight(value) {
    if (value === 0) {
        return '0%'
    }

    // Altura mínima para que los valores pequeños sigan siendo visibles.
    return `${Math.max((value / chartMax.value) * 100, 3)}%`
}

function money(value) {
    return formatMoney(value, props.currency)
}
</script>

<template>
    <article class="panel">

        <div class="panel-header">
            <div>
                <h3>Ingresos vs. gastos</h3>

                <p>Cómo se movió tu dinero mes a mes.</p>
            </div>

            <div class="legend">
                <span><i class="dot income"></i>Ingresos</span>
                <span><i class="dot expense"></i>Gastos</span>
            </div>
        </div>

        <div
            v-if="hasData"
            class="chart"
        >
            <div
                v-for="month in months"
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

                <span class="chart-label">{{ month.label }}</span>

                <span
                    class="chart-balance"
                    :class="month.balance < 0 ? 'bad' : 'good'"
                >
                    {{ month.balance < 0 ? '−' : '+' }}{{ money(Math.abs(month.balance)) }}
                </span>
            </div>
        </div>

        <p
            v-else
            class="panel-empty"
        >
            No hay ingresos ni gastos en este período.
        </p>

    </article>
</template>

<style scoped src="../styles/report-panel.css"></style>
<style scoped src="../styles/report-cash-flow.css"></style>
