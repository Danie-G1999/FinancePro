<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

import { useAuthStore } from '../../Auth/stores/authStore'
import { useLayoutStore } from '../../../components/layout/layoutStore'
import { useTourStore } from '../stores/tourStore'
import { TOUR_STEPS } from '../utils/tourSteps'

const CARD_WIDTH = 360
const GAP = 16
const MARGIN = 16
const SPOTLIGHT_PADDING = 6

const router = useRouter()
const authStore = useAuthStore()
const tourStore = useTourStore()
const layout = useLayoutStore()

// En escritorio se omiten los pasos que solo aplican al menú hamburguesa.
const steps = computed(() =>
    TOUR_STEPS.filter((item) => !item.mobileOnly || layout.isMobile)
)

// Lo que tarda en abrirse o cerrarse el menú móvil (ver .sidebar-mobile).
const MENU_TRANSITION_MS = 280

const index = ref(0)
const rect = ref(null)
const card = ref(null)
const primaryButton = ref(null)
const position = ref({ top: 0, left: 0, placement: 'center', arrow: 0 })

const step = computed(() => steps.value[Math.min(index.value, steps.value.length - 1)])
const isFirst = computed(() => index.value === 0)
const isLast = computed(() => index.value === steps.value.length - 1)

const firstName = computed(() => authStore.user?.name?.trim().split(/\s+/)[0] ?? '')

const title = computed(() =>
    typeof step.value.title === 'function'
        ? step.value.title(firstName.value)
        : step.value.title
)

// Sin objetivo, el foco se reduce a un punto en el centro: toda la pantalla queda oscura.
const spotlightStyle = computed(() => {
    if (!rect.value) {
        return {
            top: `${window.innerHeight / 2}px`,
            left: `${window.innerWidth / 2}px`,
            width: '0px',
            height: '0px',
        }
    }

    return {
        top: `${rect.value.top}px`,
        left: `${rect.value.left}px`,
        width: `${rect.value.width}px`,
        height: `${rect.value.height}px`,
    }
})

const cardStyle = computed(() => ({
    top: `${position.value.top}px`,
    left: `${position.value.left}px`,
    width: `${Math.min(CARD_WIDTH, window.innerWidth - MARGIN * 2)}px`,
    '--arrow-offset': `${position.value.arrow}px`,
}))

function clamp(value, min, max) {
    return Math.min(Math.max(value, min), Math.max(min, max))
}

function findTarget() {
    const target = step.value.target

    return target ? document.querySelector(`[data-tour="${target}"]`) : null
}

// Prueba derecha, abajo, izquierda y arriba; si nada cabe, centra la tarjeta.
function placeCard() {
    const vw = window.innerWidth
    const vh = window.innerHeight
    const w = Math.min(CARD_WIDTH, vw - MARGIN * 2)
    const h = card.value?.offsetHeight ?? 260
    const r = rect.value

    if (!r) {
        position.value = { top: (vh - h) / 2, left: (vw - w) / 2, placement: 'center', arrow: 0 }

        return
    }

    const centerY = r.top + r.height / 2
    const centerX = r.left + r.width / 2

    const vertical = (left, placement) => {
        const top = clamp(centerY - h / 2, MARGIN, vh - h - MARGIN)

        return { top, left, placement, arrow: clamp(centerY - top, 20, h - 20) }
    }

    const horizontal = (top, placement) => {
        const left = clamp(centerX - w / 2, MARGIN, vw - w - MARGIN)

        return { top, left, placement, arrow: clamp(centerX - left, 20, w - 20) }
    }

    if (r.left + r.width + GAP + w <= vw - MARGIN) {
        position.value = vertical(r.left + r.width + GAP, 'right')
    } else if (r.top + r.height + GAP + h <= vh - MARGIN) {
        position.value = horizontal(r.top + r.height + GAP, 'bottom')
    } else if (r.left - GAP - w >= MARGIN) {
        position.value = vertical(r.left - GAP - w, 'left')
    } else if (r.top - GAP - h >= MARGIN) {
        position.value = horizontal(r.top - GAP - h, 'top')
    } else {
        position.value = { top: (vh - h) / 2, left: (vw - w) / 2, placement: 'center', arrow: 0 }
    }
}

function wait(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms))
}

// En móvil, los elementos de la barra lateral solo se ven con el menú abierto.
async function syncMobileMenu(element) {
    if (!layout.isMobile) {
        return
    }

    const inSidebar = Boolean(element?.closest('#app-sidebar'))

    if (inSidebar && !layout.menuOpen) {
        layout.openMenu()
        await wait(MENU_TRANSITION_MS)
    } else if (!inSidebar && layout.menuOpen) {
        layout.closeMenu()
        await wait(MENU_TRANSITION_MS)
    }
}

// Evita que una ubicación vieja pise a la nueva si el usuario avanza rápido.
let locateRun = 0

async function locate() {
    const run = ++locateRun
    const element = findTarget()

    await syncMobileMenu(element)

    if (run !== locateRun || !tourStore.active) {
        return
    }

    if (element) {
        element.scrollIntoView({ block: 'nearest', inline: 'nearest' })

        const r = element.getBoundingClientRect()

        rect.value = {
            top: r.top - SPOTLIGHT_PADDING,
            left: r.left - SPOTLIGHT_PADDING,
            width: r.width + SPOTLIGHT_PADDING * 2,
            height: r.height + SPOTLIGHT_PADDING * 2,
        }
    } else {
        rect.value = null
    }

    // La tarjeta cambia de contenido (y de alto) antes de ubicarla.
    await nextTick()
    placeCard()
    primaryButton.value?.focus({ preventScroll: true })
}

function goTo(newIndex) {
    index.value = clamp(newIndex, 0, steps.value.length - 1)
}

function next() {
    if (isLast.value) {
        finish()
    } else {
        goTo(index.value + 1)
    }
}

function previous() {
    goTo(index.value - 1)
}

function finish(goToAccounts = false) {
    tourStore.stop()
    layout.closeMenu()
    authStore.completeOnboarding()

    if (goToAccounts) {
        router.push({ name: 'cuentas' })
    }
}

function handleKeydown(event) {
    if (!tourStore.active) {
        return
    }

    if (event.key === 'Escape') {
        finish()
    } else if (event.key === 'ArrowRight') {
        next()
    } else if (event.key === 'ArrowLeft' && !isFirst.value) {
        previous()
    }
}

function handleResize() {
    if (tourStore.active) {
        locate()
    }
}

watch(index, locate)

watch(
    () => tourStore.active,
    (active) => {
        if (active) {
            index.value = 0
            locate()
        }
    }
)

// Un usuario nuevo lo ve automáticamente al entrar, una sola vez por carga de la app.
let autoStarted = false

watch(
    () => authStore.user,
    (user) => {
        // Primero la aceptación legal; el recorrido arranca cuando ya no hay pendientes.
        const legalPending = (user?.legal?.pending?.length ?? 0) > 0

        if (user && !user.onboarding_completed && !legalPending && !autoStarted) {
            autoStarted = true

            // Espera a que el layout termine de pintarse para medir bien la barra lateral.
            setTimeout(() => tourStore.start(), 500)
        }
    },
    { immediate: true }
)

onMounted(() => {
    window.addEventListener('resize', handleResize)
    document.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize)
    document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
    <Teleport to="body">
        <Transition name="tour-fade">
            <div
                v-if="tourStore.active"
                class="tour-root"
            >
                <!-- Bloquea los clics a la app mientras dura el recorrido. -->
                <div class="tour-blocker"></div>

                <div
                    class="tour-spotlight"
                    :class="{ 'has-target': rect }"
                    :style="spotlightStyle"
                ></div>

                <div
                    ref="card"
                    class="tour-card"
                    :class="`placement-${position.placement}`"
                    :style="cardStyle"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="tour-title"
                    aria-describedby="tour-body"
                >
                    <!-- Al terminar de entrar el paso nuevo, la tarjeta ya tiene su alto real. -->
                    <Transition
                        name="tour-step"
                        mode="out-in"
                        @after-enter="placeCard"
                    >
                        <div
                            :key="index"
                            class="tour-content"
                        >
                            <div class="tour-heading">
                                <span class="tour-icon">{{ step.icon }}</span>

                                <span class="tour-counter">
                                    Paso {{ index + 1 }} de {{ steps.length }}
                                </span>
                            </div>

                            <h2 id="tour-title">{{ title }}</h2>

                            <p id="tour-body">{{ step.body }}</p>

                            <p
                                v-if="step.tip"
                                class="tour-tip"
                            >
                                💡 {{ step.tip }}
                            </p>
                        </div>
                    </Transition>

                    <div
                        class="tour-dots"
                        role="tablist"
                        aria-label="Pasos del recorrido"
                    >
                        <button
                            v-for="(item, i) in steps"
                            :key="i"
                            type="button"
                            class="tour-dot"
                            :class="{ active: i === index, done: i < index }"
                            :aria-label="`Ir al paso ${i + 1}`"
                            :aria-selected="i === index"
                            @click="goTo(i)"
                        ></button>
                    </div>

                    <footer class="tour-actions">
                        <template v-if="step.final">
                            <button
                                type="button"
                                class="tour-btn tour-btn-ghost"
                                @click="finish()"
                            >
                                Explorar por mi cuenta
                            </button>

                            <button
                                ref="primaryButton"
                                type="button"
                                class="tour-btn tour-btn-primary"
                                @click="finish(true)"
                            >
                                Crear mi primera cuenta
                            </button>
                        </template>

                        <template v-else>
                            <button
                                type="button"
                                class="tour-skip"
                                @click="finish()"
                            >
                                Saltar recorrido
                            </button>

                            <div class="tour-nav">
                                <button
                                    v-if="!isFirst"
                                    type="button"
                                    class="tour-btn tour-btn-ghost"
                                    @click="previous"
                                >
                                    Atrás
                                </button>

                                <button
                                    ref="primaryButton"
                                    type="button"
                                    class="tour-btn tour-btn-primary"
                                    @click="next"
                                >
                                    {{ isFirst ? 'Comenzar' : 'Siguiente' }}
                                </button>
                            </div>
                        </template>
                    </footer>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped src="../styles/app-tour.css"></style>
