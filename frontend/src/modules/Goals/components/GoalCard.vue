<script setup>
import { computed } from 'vue'

import { formatMoney } from '../../../utils/money'

const props = defineProps({
    goal: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['edit', 'delete'])

const formattedDate = computed(() => {
    if (!props.goal.target_date) {
        return null
    }

    const [year, month, day] = props.goal.target_date.split('-')

    return `${day}/${month}/${year}`
})
</script>

<template>
    <article class="goal-card">

        <div class="goal-card-header">

            <div class="goal-icon">
                ★
            </div>

            <div class="goal-info">
                <h3>{{ goal.name }}</h3>

                <span v-if="formattedDate">
                    Meta para el {{ formattedDate }}
                </span>

                <span v-else>
                    Sin fecha objetivo
                </span>
            </div>

            <div class="goal-actions">
                <button
                    type="button"
                    class="icon-button"
                    title="Editar meta"
                    aria-label="Editar meta"
                    @click="emit('edit', goal)"
                >
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9" />
                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                    </svg>
                </button>

                <button
                    type="button"
                    class="icon-button danger"
                    title="Eliminar meta"
                    aria-label="Eliminar meta"
                    @click="emit('delete', goal)"
                >
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18" />
                        <path d="M8 6V4h8v2" />
                        <path d="M19 6l-1 14H6L5 6" />
                        <path d="M10 11v5M14 11v5" />
                    </svg>
                </button>
            </div>

        </div>

        <div class="goal-card-body">

            <span class="goal-label">
                Ahorrado
            </span>

            <strong class="goal-saved">
                {{ formatMoney(goal.saved_amount, goal.currency) }}
            </strong>

            <span class="goal-target">
                de {{ formatMoney(goal.target_amount, goal.currency) }}
            </span>

            <div
                class="progress"
                role="progressbar"
                :aria-valuenow="goal.progress_percent"
                aria-valuemin="0"
                aria-valuemax="100"
            >
                <div
                    class="progress-bar"
                    :class="{ completed: goal.is_completed }"
                    :style="{ width: `${goal.progress_percent}%` }"
                ></div>
            </div>

        </div>

        <div class="goal-card-footer">

            <span
                class="goal-status"
                :class="{ completed: goal.is_completed }"
            >
                {{
                    goal.is_completed
                        ? '¡Meta cumplida!'
                        : `${goal.progress_percent}% · faltan ${formatMoney(goal.remaining_amount, goal.currency)}`
                }}
            </span>

            <span class="goal-contributions">
                {{ goal.contributions_count }}
                {{ goal.contributions_count === 1 ? 'aporte' : 'aportes' }}
            </span>

        </div>

    </article>
</template>

<style scoped src="../styles/goal-card.css"></style>
