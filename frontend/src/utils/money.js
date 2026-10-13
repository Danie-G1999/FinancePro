export function formatMoney(value, currency) {
    const amount = Number(value)

    try {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency,
            minimumFractionDigits: currency === 'COP' ? 0 : 2,
            maximumFractionDigits: 2,
        }).format(amount)
    } catch {
        return `${amount.toLocaleString('es-CO')} ${currency}`
    }
}

// Formato de entrada: miles con punto y decimales con coma (1.500.000,50).
export function formatMoneyInput(raw) {
    const cleaned = String(raw ?? '').replace(/[^\d,]/g, '')
    const [integer, ...rest] = cleaned.split(',')
    const decimals = rest.join('').slice(0, 2)
    const grouped = integer
        .replace(/^0+(?=\d)/, '')
        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')

    return cleaned.includes(',')
        ? `${grouped},${decimals}`
        : grouped
}

export function moneyInputToNumber(display) {
    return String(display).replace(/\./g, '').replace(',', '.')
}

export function numberToMoneyInput(value) {
    if (value === null || value === undefined || value === '') {
        return ''
    }

    return formatMoneyInput(String(value).replace('.', ','))
}
