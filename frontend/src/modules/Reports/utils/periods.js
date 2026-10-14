function toDateString(date) {
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${date.getFullYear()}-${month}-${day}`
}

export const REPORT_PRESETS = [
    { key: 'this_month', label: 'Este mes' },
    { key: 'last_month', label: 'Mes anterior' },
    { key: 'last_3_months', label: 'Últimos 3 meses' },
    { key: 'last_6_months', label: 'Últimos 6 meses' },
    { key: 'this_year', label: 'Este año' },
    { key: 'custom', label: 'Personalizado' },
]

/** Rango de fechas (YYYY-MM-DD) de un preset, calculado con la fecha local. */
export function getPresetRange(key, today = new Date()) {
    const year = today.getFullYear()
    const month = today.getMonth()

    const ranges = {
        this_month: [new Date(year, month, 1), new Date(year, month + 1, 0)],
        last_month: [new Date(year, month - 1, 1), new Date(year, month, 0)],
        last_3_months: [new Date(year, month - 2, 1), new Date(year, month + 1, 0)],
        last_6_months: [new Date(year, month - 5, 1), new Date(year, month + 1, 0)],
        this_year: [new Date(year, 0, 1), new Date(year, 11, 31)],
    }

    const [from, to] = ranges[key]

    return { from: toDateString(from), to: toDateString(to) }
}
