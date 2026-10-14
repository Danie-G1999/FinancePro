<script setup>
import BaseModal from '../../../components/common/BaseModal.vue'
import { formatMoney } from '../../../utils/money'

defineProps({
    schedules: {
        type: Array,
        required: true,
    },

    loading: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close', 'edit', 'toggle', 'delete'])

function formatDate(value) {
    const [year, month, day] = value.split('-')

    return `${day}/${month}/${year}`
}
</script>

<template>
    <BaseModal
        title="Ingresos recurrentes"
        subtitle="Salarios y otros ingresos que llegan cada mes."
        max-width="640px"
        @close="emit('close')"
    >
        <p
            v-if="loading && schedules.length === 0"
            class="state"
        >
            Cargando...
        </p>

        <p
            v-else-if="schedules.length === 0"
            class="state"
        >
            Aún no tienes ingresos recurrentes. Crea uno desde
            «Nuevo ingreso» marcando «Sí» en «¿Es un ingreso recurrente?».
        </p>

        <ul
            v-else
            class="schedules"
        >
            <li
                v-for="schedule in schedules"
                :key="schedule.id"
                class="schedule"
                :class="{ paused: !schedule.is_active }"
            >
                <div class="schedule-info">
                    <div class="schedule-title">
                        <strong>{{ schedule.description }}</strong>

                        <span class="badge">
                            {{ schedule.type_label }}
                        </span>

                        <span
                            v-if="!schedule.is_active"
                            class="badge paused-badge"
                        >
                            En pausa
                        </span>
                    </div>

                    <span class="schedule-meta">
                        Día {{ schedule.day_of_month }} de cada mes
                        · {{ schedule.account_name }}
                    </span>

                    <span
                        v-if="schedule.next_payment_date"
                        class="schedule-meta"
                    >
                        Próximo ingreso: {{ formatDate(schedule.next_payment_date) }}
                    </span>
                </div>

                <strong class="schedule-amount">
                    {{ formatMoney(schedule.amount, schedule.currency) }}
                </strong>

                <div class="schedule-actions">
                    <button
                        type="button"
                        class="link-button"
                        @click="emit('toggle', schedule)"
                    >
                        {{ schedule.is_active ? 'Pausar' : 'Activar' }}
                    </button>

                    <button
                        type="button"
                        class="link-button"
                        @click="emit('edit', schedule)"
                    >
                        Editar
                    </button>

                    <button
                        type="button"
                        class="link-button danger"
                        @click="emit('delete', schedule)"
                    >
                        Eliminar
                    </button>
                </div>
            </li>
        </ul>
    </BaseModal>
</template>

<style scoped src="../styles/recurring-incomes-modal.css"></style>
