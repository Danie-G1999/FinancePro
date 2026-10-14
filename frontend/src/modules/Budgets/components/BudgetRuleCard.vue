<script setup>
import { computed } from 'vue'

import { formatMoney } from '../../../utils/money'

const props = defineProps({
    block: {
        type: Object,
        required: true,
    },

    description: {
        type: String,
        default: '',
    },

    currency: {
        type: String,
        required: true,
    },
})

const barWidth = computed(() => {
    // Con límite 0 y gasto registrado, la barra se muestra completa.
    const usage = props.block.usage_percent ?? (props.block.spent > 0 ? 100 : 0)

    return `${Math.min(usage, 100)}%`
})

const remainingLabel = computed(() =>
    props.block.remaining >= 0 ? 'Disponible' : 'Excedido en'
)

function money(value) {
    return formatMoney(Math.abs(value), props.currency)
}
</script>

<template>
    <article class="rule-card">

        <header>
            <div>
                <h3>{{ block.label }}</h3>

                <span>{{ description }}</span>
            </div>

            <span class="percent-badge">
                {{ block.percent }}%
            </span>
        </header>

        <div class="rule-amounts">
            <div>
                <span>Asignado</span>
                <strong>{{ money(block.limit) }}</strong>
            </div>

            <div>
                <span>Gastado</span>
                <strong>{{ money(block.spent) }}</strong>
            </div>

            <div>
                <span>{{ remainingLabel }}</span>
                <strong :class="{ over: block.remaining < 0 }">
                    {{ money(block.remaining) }}
                </strong>
            </div>
        </div>

        <div
            class="progress"
            role="progressbar"
            :aria-valuenow="block.usage_percent ?? 0"
            aria-valuemin="0"
            aria-valuemax="100"
        >
            <div
                class="progress-bar"
                :class="block.status"
                :style="{ width: barWidth }"
            ></div>
        </div>

        <footer :class="block.status">
            <template v-if="block.usage_percent !== null">
                {{ block.usage_percent }}% utilizado
            </template>

            <template v-else>
                Sin ingresos para asignar
            </template>
        </footer>

    </article>
</template>

<style scoped src="../styles/budget-rule-card.css"></style>
