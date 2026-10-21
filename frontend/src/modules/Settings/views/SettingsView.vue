<script setup>
import { computed, reactive, ref, watch } from 'vue'

import { useAuthStore } from '../../Auth/stores/authStore'
import { CURRENCIES } from '../../../utils/currencies'
import { formatMoney } from '../../../utils/money'
import {
    extractErrorMessage,
    showError,
    showSuccess,
} from '../../../utils/alerts'

const authStore = useAuthStore()

const TIMEOUT_OPTIONS = [5, 10, 15, 30]

const BLOCKS = [
    { key: 'need', name: 'Necesidades', hint: 'Vivienda, mercado, servicios, transporte, salud.', color: '#2563eb' },
    { key: 'want', name: 'Deseos', hint: 'Restaurantes, entretenimiento, viajes, suscripciones.', color: '#d97706' },
    { key: 'savings', name: 'Ahorro y deudas', hint: 'Fondo de emergencia, inversiones, abonos a deudas.', color: '#16a34a' },
]

const saving = ref(false)
const errors = ref({})

function initialForm() {
    const settings = authStore.user?.settings ?? {}

    return {
        currency: settings.currency ?? 'COP',
        budget_method: settings.budget_method ?? '50_30_20',
        need: settings.custom_percents?.need ?? 50,
        want: settings.custom_percents?.want ?? 30,
        savings: settings.custom_percents?.savings ?? 20,
        auto_logout_inactive: settings.auto_logout_inactive ?? true,
        inactivity_timeout_minutes: settings.inactivity_timeout_minutes ?? 5,
    }
}

const form = reactive(initialForm())

// Si /auth/me se recarga (p. ej. tras guardar en otra pestaña), el formulario se actualiza.
watch(() => authStore.user?.settings, () => Object.assign(form, initialForm()))

const isCustom = computed(() => form.budget_method === 'custom')

const activePercents = computed(() =>
    isCustom.value
        ? { need: Number(form.need) || 0, want: Number(form.want) || 0, savings: Number(form.savings) || 0 }
        : { need: 50, want: 30, savings: 20 }
)

const percentTotal = computed(
    () => activePercents.value.need + activePercents.value.want + activePercents.value.savings
)

// Vista previa con un ingreso de ejemplo en la moneda elegida.
const SAMPLE_INCOME = { COP: 3000000, USD: 3000, EUR: 3000, MXN: 30000 }

const sampleIncome = computed(() => SAMPLE_INCOME[form.currency] ?? 3000)

const dirty = computed(() => JSON.stringify(form) !== JSON.stringify(initialForm()))

function validate() {
    errors.value = {}

    if (isCustom.value) {
        for (const block of BLOCKS) {
            const value = Number(form[block.key])

            if (!Number.isInteger(value) || value < 0 || value > 100) {
                errors.value.percents = 'Cada porcentaje debe ser un número entero entre 0 y 100.'
            }
        }

        if (!errors.value.percents && percentTotal.value !== 100) {
            errors.value.percents = `Los porcentajes deben sumar 100% (ahora suman ${percentTotal.value}%).`
        }
    }

    return Object.keys(errors.value).length === 0
}

async function save() {
    if (!validate()) {
        return
    }

    saving.value = true

    try {
        await authStore.updateSettings({
            currency: form.currency,
            budget_method: form.budget_method,
            ...(isCustom.value && {
                budget_need_percent: Number(form.need),
                budget_want_percent: Number(form.want),
                budget_savings_percent: Number(form.savings),
            }),
            auto_logout_inactive: form.auto_logout_inactive,
            inactivity_timeout_minutes: Number(form.inactivity_timeout_minutes),
        })

        showSuccess('Configuración guardada correctamente.')
    } catch (error) {
        const fields = error.response?.data?.errors ?? {}

        if (fields.budget_need_percent) {
            errors.value.percents = fields.budget_need_percent[0]
        }

        showError(extractErrorMessage(error, 'No fue posible guardar la configuración.'))
    } finally {
        saving.value = false
    }
}

function reset() {
    Object.assign(form, initialForm())
    errors.value = {}
}
</script>

<template>
    <div class="settings-page">

        <!-- HEADER -->

        <header>
            <h1 class="page-title">
                Configuración
            </h1>

            <p class="page-description">
                Personaliza cómo se muestran tus finanzas y la seguridad de tu sesión.
            </p>
        </header>

        <form
            class="settings-layout"
            @submit.prevent="save"
        >

            <!-- MONEDA -->

            <section class="form-card card-currency">
                <div class="form-card-header">
                    <h2>Moneda preferida</h2>

                    <p>
                        Se usa por defecto al crear cuentas, deudas y metas, y para mostrar el
                        dashboard, el presupuesto y los reportes.
                    </p>
                </div>

                <div class="form-card-body">
                    <div class="form-group">
                        <label for="settings-currency" class="form-label">
                            Moneda
                        </label>

                        <select
                            id="settings-currency"
                            v-model="form.currency"
                            class="form-input"
                            :disabled="saving"
                        >
                            <option
                                v-for="currency in CURRENCIES"
                                :key="currency.code"
                                :value="currency.code"
                            >
                                {{ currency.code }} - {{ currency.name }}
                            </option>
                        </select>

                        <span class="form-help">
                            Ejemplo: {{ formatMoney(1250000.5, form.currency) }}. Las cuentas en
                            otra moneda siguen mostrándose en la suya.
                        </span>
                    </div>
                </div>
            </section>


            <!-- SEGURIDAD -->

            <section class="form-card card-security">
                <div class="form-card-header">
                    <h2>Seguridad de la sesión</h2>

                    <p>Protege tus datos si dejas el equipo desatendido.</p>
                </div>

                <div class="form-card-body security-body">
                    <label class="switch-container">
                        <input
                            v-model="form.auto_logout_inactive"
                            type="checkbox"
                            :disabled="saving"
                        />

                        <span class="switch"></span>

                        <span class="switch-content">
                            <span class="switch-label">
                                Cierre de sesión automático por inactividad
                            </span>

                            <span class="switch-help">
                                Recomendado en equipos compartidos. Te avisaremos un minuto antes.
                            </span>
                        </span>
                    </label>

                    <div class="form-group timeout-group">
                        <label for="settings-timeout" class="form-label">
                            Cerrar la sesión después de
                        </label>

                        <select
                            id="settings-timeout"
                            v-model.number="form.inactivity_timeout_minutes"
                            class="form-input"
                            :disabled="saving || !form.auto_logout_inactive"
                        >
                            <option
                                v-for="minutes in TIMEOUT_OPTIONS"
                                :key="minutes"
                                :value="minutes"
                            >
                                {{ minutes }} minutos sin actividad
                            </option>
                        </select>
                    </div>
                </div>
            </section>


            <!-- PRESUPUESTO -->

            <section class="form-card card-budget">
                <div class="form-card-header">
                    <h2>Metodología de presupuesto</h2>

                    <p>Define cómo se reparte tu ingreso mensual en el módulo de Presupuestos.</p>
                </div>

                <div class="form-card-body">
                    <div
                        class="method-options"
                        role="radiogroup"
                        aria-label="Metodología de presupuesto"
                    >
                        <label
                            class="method-option"
                            :class="{ active: form.budget_method === '50_30_20' }"
                        >
                            <input
                                v-model="form.budget_method"
                                type="radio"
                                value="50_30_20"
                                :disabled="saving"
                            />

                            <span class="method-title">Regla 50/30/20</span>
                            <span class="method-text">
                                El método clásico: 50% necesidades, 30% deseos y 20% ahorro.
                            </span>
                        </label>

                        <label
                            class="method-option"
                            :class="{ active: isCustom }"
                        >
                            <input
                                v-model="form.budget_method"
                                type="radio"
                                value="custom"
                                :disabled="saving"
                            />

                            <span class="method-title">Personalizado</span>
                            <span class="method-text">
                                Tú decides cuánto va a cada bloque, por ejemplo 60/20/20.
                            </span>
                        </label>
                    </div>

                    <div class="percent-grid">
                        <div
                            v-for="block in BLOCKS"
                            :key="block.key"
                            class="percent-item"
                            :style="{ '--block-color': block.color }"
                        >
                            <label :for="`percent-${block.key}`" class="form-label">
                                {{ block.name }}
                            </label>

                            <div class="percent-input">
                                <input
                                    v-if="isCustom"
                                    :id="`percent-${block.key}`"
                                    v-model.number="form[block.key]"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="1"
                                    inputmode="numeric"
                                    class="form-input"
                                    :class="{ 'input-error': errors.percents }"
                                    :disabled="saving"
                                />

                                <!-- Con 50/30/20 solo se muestran los porcentajes fijos. -->
                                <input
                                    v-else
                                    :id="`percent-${block.key}`"
                                    :value="activePercents[block.key]"
                                    type="number"
                                    class="form-input"
                                    disabled
                                />

                                <span>%</span>
                            </div>

                            <span class="form-help">{{ block.hint }}</span>

                            <span class="percent-sample">
                                {{ formatMoney(sampleIncome * activePercents[block.key] / 100, form.currency) }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="percent-bar"
                        aria-hidden="true"
                    >
                        <span
                            v-for="block in BLOCKS"
                            :key="block.key"
                            :style="{
                                width: `${Math.min(activePercents[block.key], 100)}%`,
                                background: block.color,
                            }"
                        ></span>
                    </div>

                    <p
                        class="percent-total"
                        :class="{ invalid: percentTotal !== 100 }"
                    >
                        Total: {{ percentTotal }}% · Ejemplo con un ingreso de
                        {{ formatMoney(sampleIncome, form.currency) }}
                    </p>

                    <span v-if="errors.percents" class="form-error">
                        {{ errors.percents }}
                    </span>
                </div>
            </section>


            <!-- ACCIONES -->

            <div class="form-actions">
                <button
                    type="button"
                    class="btn btn-secondary"
                    :disabled="saving || !dirty"
                    @click="reset"
                >
                    Descartar cambios
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                    :disabled="saving || !dirty"
                >
                    {{ saving ? 'Guardando...' : 'Guardar configuración' }}
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped src="../styles/settings-view.css"></style>
