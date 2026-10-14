<script setup>
import { formatMoney } from '../../../utils/money'

const props = defineProps({
    expenses: {
        type: Array,
        required: true,
    },

    currency: {
        type: String,
        required: true,
    },
})

function formatDate(value) {
    const [, month, day] = value.split('-')

    return `${day}/${month}`
}

function money(value) {
    return formatMoney(value, props.currency)
}
</script>

<template>
    <article class="panel">

        <div class="panel-header">
            <div>
                <h3>Mayores gastos</h3>

                <p>Los 5 gastos más altos del período.</p>
            </div>
        </div>

        <ol
            v-if="expenses.length > 0"
            class="expenses"
        >
            <li
                v-for="(expense, index) in expenses"
                :key="expense.id"
                class="expense"
            >
                <span class="rank">{{ index + 1 }}</span>

                <div class="expense-info">
                    <strong>{{ expense.description }}</strong>

                    <span>
                        {{ expense.account_name }}
                        · {{ expense.category }}
                        · {{ formatDate(expense.date) }}
                    </span>
                </div>

                <strong class="expense-amount">
                    {{ money(expense.amount) }}
                </strong>
            </li>
        </ol>

        <p
            v-else
            class="panel-empty"
        >
            No registraste gastos en este período.
        </p>

    </article>
</template>

<style scoped src="../styles/report-panel.css"></style>
<style scoped src="../styles/report-top-expenses.css"></style>
