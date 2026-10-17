import { defineStore } from 'pinia'
import { ref } from 'vue'

// Debajo de este ancho la barra lateral se vuelve un menú deslizable (hamburguesa).
export const MOBILE_BREAKPOINT = 1024

export const useLayoutStore = defineStore('layout', () => {
    const query = window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT}px)`)

    const isMobile = ref(query.matches)

    const menuOpen = ref(false)

    // Al pasar a escritorio el menú queda fijo, así que se cierra el estado móvil.
    query.addEventListener('change', (event) => {
        isMobile.value = event.matches

        if (!event.matches) {
            menuOpen.value = false
        }
    })

    function openMenu() {
        menuOpen.value = true
    }

    function closeMenu() {
        menuOpen.value = false
    }

    function toggleMenu() {
        menuOpen.value = !menuOpen.value
    }

    return {
        isMobile,
        menuOpen,
        openMenu,
        closeMenu,
        toggleMenu,
    }
})
