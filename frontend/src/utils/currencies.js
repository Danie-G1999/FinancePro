// Monedas disponibles en la app (mismo listado que backend/app/Modules/Settings/Support/Currencies.php).
export const CURRENCIES = [
    { code: 'COP', name: 'Peso colombiano' },
    { code: 'USD', name: 'Dólar estadounidense' },
    { code: 'EUR', name: 'Euro' },
    { code: 'MXN', name: 'Peso mexicano' },
]

export const DEFAULT_CURRENCY = 'COP'

export function currencyLabel(code) {
    const currency = CURRENCIES.find((item) => item.code === code)

    return currency ? `${currency.code} - ${currency.name}` : code
}
