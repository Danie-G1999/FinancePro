import { computed, onBeforeUnmount, ref } from 'vue'

// Cuenta regresiva tras un 429 del backend: deshabilita el envío hasta que pase el bloqueo.
export function useLockout() {
    const secondsLeft = ref(0)

    let timer = null

    function stop() {
        clearInterval(timer)
        timer = null
    }

    function start(seconds) {
        stop()

        secondsLeft.value = Math.max(1, Number(seconds) || 60)

        timer = setInterval(() => {
            secondsLeft.value -= 1

            if (secondsLeft.value <= 0) {
                stop()
            }
        }, 1000)
    }

    const locked = computed(() => secondsLeft.value > 0)

    onBeforeUnmount(stop)

    return { secondsLeft, locked, start }
}

// Primer mensaje de cada campo de un error 422 de Laravel.
export function fieldErrors(error) {
    return Object.fromEntries(
        Object.entries(error.response?.data?.errors ?? {}).map(
            ([field, messages]) => [field, messages[0]]
        )
    )
}
