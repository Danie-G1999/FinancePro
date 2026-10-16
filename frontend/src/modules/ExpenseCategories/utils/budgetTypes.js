// Bloques de la regla 50/30/20, en el mismo orden y con los mismos valores del backend.
export const BUDGET_TYPES = [
    { value: 'need', label: 'Necesidad (50%)', color: '#2563eb' },
    { value: 'want', label: 'Deseo (30%)', color: '#d97706' },
    { value: 'savings', label: 'Ahorro (20%)', color: '#16a34a' },
]

export function budgetTypeLabel(value) {
    return BUDGET_TYPES.find((type) => type.value === value)?.label ?? value
}

export function budgetTypeColor(value) {
    return BUDGET_TYPES.find((type) => type.value === value)?.color ?? '#6b7280'
}
