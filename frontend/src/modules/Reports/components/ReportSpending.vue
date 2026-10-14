<script setup>
import { computed } from 'vue'

import { formatMoney } from '../../../utils/money'

const props = defineProps({
    spending: {
        type: Object,
        required: true,
    },

    currency: {
        type: String,
        required: true,
    },
})

const BLOCK_ORDER = ['need', 'want', 'savings']

const blocks = computed(() =>
    BLOCK_ORDER.map((key) => ({ key, ...props.spending.by_category[key] }))
)

function money(value) {
    return formatMoney(value, props.currency)
}

// Solo marca exceso en necesidades y deseos; ahorro y deudas no debe "limitarse".
function exceedsRecommendation(block) {
    return (
        block.key !== 'savings' &&
        block.income_share !== null &&
        block.income_share > block.recommended_percent
    )
}
</script>

<template>
    <article class="panel">

        <div class="panel-header">
            <div>
                <h3>¿En qué se va tu dinero?</h3>

                <p>Distribución de tus salidas según la regla 50/30/20.</p>
            </div>

            <strong>{{ money(spending.total) }}</strong>
        </div>

        <div
            v-if="spending.total > 0"
            class="panel-body"
        >

            <div class="stacked" aria-hidden="true">
                <div
                    v-for="block in blocks"
                    :key="block.key"
                    class="stacked-part"
                    :class="block.key"
                    :style="{ width: `${block.share}%` }"
                ></div>
            </div>

            <ul class="blocks">
                <li
                    v-for="block in blocks"
                    :key="block.key"
                >
                    <div class="block-title">
                        <i class="swatch" :class="block.key"></i>

                        <span>{{ block.label }}</span>

                        <strong>{{ money(block.amount) }}</strong>
                    </div>

                    <small :class="{ over: exceedsRecommendation(block) }">
                        {{ block.share }}% de tus salidas
                        <template v-if="block.income_share !== null">
                            · {{ block.income_share }}% de tus ingresos
                            (recomendado {{ block.recommended_percent }}%)
                        </template>
                    </small>
                </li>
            </ul>

            <div class="split">
                <div>
                    <span>Gastos fijos</span>
                    <strong>{{ money(spending.fixed) }}</strong>
                </div>

                <div>
                    <span>Gastos variables</span>
                    <strong>{{ money(spending.variable) }}</strong>
                </div>

                <div>
                    <span>Enviado a metas</span>
                    <strong>{{ money(spending.goal_contributions) }}</strong>
                </div>
            </div>

        </div>

        <p
            v-else
            class="panel-empty"
        >
            No registraste gastos en este período.
        </p>

    </article>
</template>

<style scoped src="../styles/report-panel.css"></style>
<style scoped src="../styles/report-spending.css"></style>
