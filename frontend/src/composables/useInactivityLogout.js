import { computed, onBeforeUnmount, ref, watch } from 'vue'

import api from '../api/axios'
import { useAuthStore } from '../modules/Auth/stores/authStore'

const ACTIVITY_EVENTS = ['mousemove', 'mousedown', 'keydown', 'click', 'scroll', 'touchstart', 'wheel']

// Compartido entre pestañas: actividad en una mantiene viva la sesión en las demás.
const STORAGE_KEY = 'financepro:last-activity'

const CHECK_EVERY_MS = 1000

// Aviso antes del cierre para que el usuario pueda seguir conectado.
const WARNING_SECONDS = 60

// No escribir en cada movimiento del mouse.
const WRITE_THROTTLE_MS = 5000

// El servidor solo ve peticiones a la API: mientras el usuario usa la interfaz sin hacerlas
// (leer el recorrido, llenar un formulario largo) se le avisa que sigue activo.
const HEARTBEAT_MAX_MS = 60 * 1000

function readShared() {
    try {
        return Number(localStorage.getItem(STORAGE_KEY)) || 0
    } catch {
        return 0
    }
}

function writeShared(timestamp) {
    try {
        localStorage.setItem(STORAGE_KEY, String(timestamp))
    } catch {
        // Almacenamiento bloqueado: el control sigue funcionando en esta pestaña.
    }
}

/**
 * Cierra la sesión tras N minutos sin interacción si el usuario lo tiene activado
 * en Configuración. El backend aplica el mismo límite (EnforceInactivityTimeout).
 */
export function useInactivityLogout() {
    const authStore = useAuthStore()

    const enabled = computed(() => Boolean(authStore.user?.settings?.auto_logout_inactive))

    const timeoutMs = computed(
        () => (authStore.user?.settings?.inactivity_timeout_minutes ?? 5) * 60 * 1000
    )

    const secondsLeft = ref(null)

    const showWarning = computed(() => secondsLeft.value !== null && secondsLeft.value <= WARNING_SECONDS)

    let lastActivity = Date.now()
    let lastWrite = 0
    let lastHeartbeat = Date.now()
    let timer = null
    let loggingOut = false

    // Varias veces dentro del límite, para que el servidor nunca vea la sesión como abandonada.
    const heartbeatEvery = computed(() => Math.min(HEARTBEAT_MAX_MS, timeoutMs.value / 3))

    function sendHeartbeat(now) {
        lastHeartbeat = now

        // Un 401 (sesión ya vencida en el servidor) lo maneja el interceptor: lleva al login.
        api.post('/user/session/heartbeat').catch(() => {})
    }

    function registerActivity() {
        const now = Date.now()

        lastActivity = now

        if (now - lastWrite > WRITE_THROTTLE_MS) {
            lastWrite = now
            writeShared(now)
        }

        if (!loggingOut && now - lastHeartbeat >= heartbeatEvery.value) {
            sendHeartbeat(now)
        }
    }

    function check() {
        // La actividad de otra pestaña cuenta como propia.
        const latest = Math.max(lastActivity, readShared())
        const remaining = timeoutMs.value - (Date.now() - latest)

        if (remaining <= 0) {
            expire()
            return
        }

        secondsLeft.value = Math.ceil(remaining / 1000)
    }

    async function expire() {
        if (loggingOut) {
            return
        }

        loggingOut = true
        stop()

        await authStore.logoutAndRedirect('inactive')
    }

    function onVisibilityChange() {
        // Al volver a la pestaña (o despertar el equipo) se revisa de inmediato.
        if (document.visibilityState === 'visible') {
            check()
        }
    }

    function start() {
        stop()

        lastActivity = Date.now()
        lastHeartbeat = lastActivity
        writeShared(lastActivity)

        ACTIVITY_EVENTS.forEach((event) =>
            window.addEventListener(event, registerActivity, { passive: true })
        )
        document.addEventListener('visibilitychange', onVisibilityChange)

        timer = setInterval(check, CHECK_EVERY_MS)
        check()
    }

    function stop() {
        clearInterval(timer)
        timer = null
        secondsLeft.value = null

        ACTIVITY_EVENTS.forEach((event) => window.removeEventListener(event, registerActivity))
        document.removeEventListener('visibilitychange', onVisibilityChange)
    }

    // "Seguir conectado" en el aviso.
    function stayConnected() {
        lastWrite = 0
        // Confirmación explícita: se avisa al servidor aunque haya pasado poco desde el último aviso.
        lastHeartbeat = 0
        registerActivity()
        check()
    }

    watch(
        [enabled, timeoutMs],
        ([isEnabled]) => (isEnabled ? start() : stop()),
        { immediate: true }
    )

    onBeforeUnmount(stop)

    return { showWarning, secondsLeft, stayConnected }
}
