<script setup>
import { computed } from 'vue'

import { formatMoney } from '../../../utils/money'

const props = defineProps({
    formula: {
        type: Object,
        required: true,
    },

    currency: {
        type: String,
        required: true,
    },
})

const isDeficit = computed(() => props.formula.surplus < 0)

function money(value) {
    return formatMoney(value, props.currency)
}
</script>

<template>
    <section class="formula-card">

        <div class="formula-item">
            <span>Ingresos</span>
            <strong class="positive">{{ money(formula.income) }}</strong>
        </div>

        <span class="operator" aria-hidden="true">−</span>

        <div class="formula-item">
            <span>Gastos fijos</span>
            <strong>{{ money(formula.fixed_expenses) }}</strong>
        </div>

        <span class="operator" aria-hidden="true">−</span>

        <div class="formula-item">
            <span>Gastos variables</span>
            <strong>{{ money(formula.variable_expenses) }}</strong>
        </div>

        <span class="operator" aria-hidden="true">=</span>

        <div
            class="formula-item result"
            :class="isDeficit ? 'deficit' : 'surplus'"
        >
            <span>{{ isDeficit ? 'Déficit' : 'Superávit' }}</span>
            <strong>{{ money(formula.surplus) }}</strong>
            <small>
                {{
                    isDeficit
                        ? 'Gastas más de lo que ingresas'
                        : 'Disponible para ahorro o inversión'
                }}
            </small>
        </div>

    </section>
</template>

<style scoped src="../styles/budget-formula.css"></style>
