<script setup>
import { formatMoney } from '../../utils/money'

defineProps({
    items: {
        type: Array,
        required: true,
    },

    meta: {
        type: Object,
        required: true,
    },

    loading: {
        type: Boolean,
        default: false,
    },

    // Campo del item que contiene la fecha del movimiento.
    dateKey: {
        type: String,
        required: true,
    },

    variant: {
        type: String,
        default: 'income',
        validator: (value) => ['income', 'expense', 'mixed'].includes(value),
    },

    // Acciones por fila: edit, delete y/o view.
    actions: {
        type: Array,
        default: () => ['edit', 'delete'],
    },

    // Muestra una columna con el tipo (ingreso o gasto) de cada fila.
    showType: {
        type: Boolean,
        default: false,
    },

    // Plural usado en el pie, p. ej. "ingresos".
    itemLabel: {
        type: String,
        default: 'registros',
    },
})

const emit = defineEmits(['edit', 'delete', 'view', 'page-change'])

function amountClass(item, variant) {
    return `amount-${variant === 'mixed' ? item.type : variant}`
}

function formatDate(value) {
    if (!value) {
        return ''
    }

    const [year, month, day] = value.split('-')

    return `${day}/${month}/${year}`
}
</script>

<template>
    <section class="table-card">

        <div class="table-scroll">

            <table class="transaction-table">

                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th v-if="showType">Tipo</th>
                        <th>Descripción</th>
                        <th>Cuenta</th>
                        <th class="text-right">Valor</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="item in items"
                        :key="item.id"
                    >
                        <td class="nowrap">
                            {{ formatDate(item[dateKey]) }}
                        </td>

                        <td v-if="showType">
                            <span
                                class="type-badge"
                                :class="item.type"
                            >
                                {{ item.type_label }}
                            </span>
                        </td>

                        <td>
                            <span class="cell-title">
                                {{ item.description }}
                            </span>

                            <span
                                v-if="item.debt_name"
                                class="cell-debt"
                            >
                                Pago de deuda: {{ item.debt_name }}
                            </span>

                            <span
                                v-if="item.goal_name"
                                class="cell-debt"
                            >
                                Aporte a meta: {{ item.goal_name }}
                            </span>

                            <span
                                v-if="item.recurring_label"
                                class="cell-debt"
                            >
                                {{ item.recurring_label }}
                            </span>

                            <span
                                v-if="item.notes"
                                class="cell-notes"
                            >
                                {{ item.notes }}
                            </span>
                        </td>

                        <td>
                            {{ item.account_name }}
                        </td>

                        <td
                            class="text-right"
                            :class="amountClass(item, variant)"
                        >
                            {{ variant === 'mixed' ? (item.type === 'income' ? '+' : '-') : '' }}{{ formatMoney(item.amount, item.currency) }}
                        </td>

                        <td class="text-right">
                            <div class="row-actions">
                                <button
                                    v-if="actions.includes('view')"
                                    type="button"
                                    class="icon-button"
                                    title="Ver detalle"
                                    aria-label="Ver detalle"
                                    @click="emit('view', item)"
                                >
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>

                                <button
                                    v-if="actions.includes('edit')"
                                    type="button"
                                    class="icon-button"
                                    title="Editar"
                                    aria-label="Editar"
                                    @click="emit('edit', item)"
                                >
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9" />
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                    </svg>
                                </button>

                                <button
                                    v-if="actions.includes('delete')"
                                    type="button"
                                    class="icon-button danger"
                                    title="Eliminar"
                                    aria-label="Eliminar"
                                    @click="emit('delete', item)"
                                >
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4h8v2" />
                                        <path d="M19 6l-1 14H6L5 6" />
                                        <path d="M10 11v5M14 11v5" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

        <footer class="table-footer">

            <span class="pagination-info">
                Mostrando {{ meta.from }}
                a {{ meta.to }}
                de {{ meta.total }} {{ itemLabel }}
            </span>

            <div class="pagination">
                <button
                    type="button"
                    class="btn btn-secondary"
                    :disabled="loading || meta.current_page <= 1"
                    @click="emit('page-change', meta.current_page - 1)"
                >
                    Anterior
                </button>

                <span class="pagination-page">
                    {{ meta.current_page }} / {{ meta.last_page }}
                </span>

                <button
                    type="button"
                    class="btn btn-secondary"
                    :disabled="loading || meta.current_page >= meta.last_page"
                    @click="emit('page-change', meta.current_page + 1)"
                >
                    Siguiente
                </button>
            </div>

        </footer>

    </section>
</template>

<style scoped src="./transaction-table.css"></style>
