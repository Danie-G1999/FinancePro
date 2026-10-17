import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useTourStore = defineStore('tour', () => {
    const active = ref(false)

    function start() {
        active.value = true
    }

    function stop() {
        active.value = false
    }

    return {
        active,
        start,
        stop,
    }
})
