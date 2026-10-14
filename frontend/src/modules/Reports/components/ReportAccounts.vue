<script setup>
import { formatMoney } from '../../../utils/money'

const props = defineProps({
    accounts: {
        type: Object,
        required: true,
    },

    currency: {
        type: String,
        required: true,
    },
})

function money(value) {
    return formatMoney(value, props.currency)
}
</script>

<template>
    <article class="panel">

        <div class="panel-header">
            <div>
                <h3>Tus cuentas</h3>

                <p>Saldo actual y movimientos del período.</p>
            </div>

            <strong>{{ money(accounts.total_balance) }}</strong>
        </div>

        <ul
            v-if="accounts.items.length > 0"
            class="accounts"
        >
            <li
                v-for="account in accounts.items"
                :key="account.id"
                class="account"
            >
                <div class="account-main">
                    <div>
                        <strong>{{ account.name }}</strong>

                        <span>
                            {{ account.type_label }}
                            <template v-if="account.institution">
                                · {{ account.institution }}
                            </template>
                        </span>
                    </div>

                    <strong
                        class="account-balance"
                        :class="{ negative: account.balance < 0 }"
                    >
                        {{ money(account.balance) }}
                    </strong>
                </div>

                <div
                    class="progress"
                    :title="`${account.share_percent}% de tu saldo total`"
                >
                    <div
                        class="progress-bar"
                        :style="{ width: `${account.share_percent}%` }"
                    ></div>
                </div>

                <div class="account-flow">
                    <span class="in">
                        Entró {{ money(account.income) }}
                    </span>

                    <span class="out">
                        Salió {{ money(account.expense) }}
                    </span>

                    <span>
                        {{ account.share_percent }}% del total
                    </span>
                </div>
            </li>
        </ul>

        <p
            v-else
            class="panel-empty"
        >
            No tienes cuentas activas en esta moneda.
        </p>

    </article>
</template>

<style scoped src="../styles/report-panel.css"></style>
<style scoped src="../styles/report-accounts.css"></style>
