import { computed } from 'vue'

import { useAuthStore } from '../../Auth/stores/authStore'

// Bloques del presupuesto, en el mismo orden y con los mismos valores del backend.
// El porcentaje de cada uno depende del método elegido en Configuración (50/30/20 o personalizado).
const BLOCKS = [
    { value: 'need', name: 'Necesidad', color: '#2563eb', percent: 50 },
    { value: 'want', name: 'Deseo', color: '#d97706', percent: 30 },
    { value: 'savings', name: 'Ahorro', color: '#16a34a', percent: 20 },
]

// Etiquetas con los porcentajes 50/30/20 (para contextos sin usuario).
export const BUDGET_TYPES = BLOCKS.map((block) => ({
    ...block,
    label: `${block.name} (${block.percent}%)`,
}))

/** Bloques con los porcentajes que el usuario tiene configurados. */
export function useBudgetTypes() {
    const authStore = useAuthStore()

    const percents = computed(() => authStore.user?.settings?.budget_percents ?? {})

    const budgetTypes = computed(() =>
        BLOCKS.map((block) => {
            const percent = percents.value[block.value] ?? block.percent

            return { ...block, percent, label: `${block.name} (${percent}%)` }
        })
    )

    // "50/30/20" o el reparto personalizado, p. ej. "60/20/20".
    const ruleLabel = computed(() => budgetTypes.value.map((block) => block.percent).join('/'))

    return { budgetTypes, ruleLabel }
}

export function budgetTypeLabel(value) {
    return BUDGET_TYPES.find((type) => type.value === value)?.label ?? value
}

export function budgetTypeColor(value) {
    return BUDGET_TYPES.find((type) => type.value === value)?.color ?? '#6b7280'
}
