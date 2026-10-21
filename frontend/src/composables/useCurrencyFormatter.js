import { computed } from 'vue'

import { useAuthStore } from '../modules/Auth/stores/authStore'
import { DEFAULT_CURRENCY } from '../utils/currencies'
import { formatMoney } from '../utils/money'

/**
 * Formatea valores con la moneda preferida del usuario.
 *
 * Los registros que tienen moneda propia (una cuenta en USD, una deuda en EUR) se formatean
 * con SU moneda: `format(cuenta.balance, cuenta.currency)`. La preferida se usa cuando el valor
 * no trae moneda y como valor por defecto en los formularios.
 */
export function useCurrencyFormatter() {
    const authStore = useAuthStore()

    const preferredCurrency = computed(
        () => authStore.user?.settings?.currency ?? DEFAULT_CURRENCY
    )

    function format(value, currency = null) {
        return formatMoney(value, currency || preferredCurrency.value)
    }

    return { preferredCurrency, format }
}
