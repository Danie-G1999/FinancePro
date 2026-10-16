<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

import { ICON_GROUPS } from '../utils/iconCatalog'

const model = defineModel({
    type: String,
    default: '',
})

defineProps({
    id: {
        type: String,
        default: undefined,
    },

    disabled: {
        type: Boolean,
        default: false,
    },
})

const root = ref(null)
const searchInput = ref(null)

const open = ref(false)
const search = ref('')
const activeGroup = ref(ICON_GROUPS[0].name)

function normalize(text) {
    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
}

// Con búsqueda se muestran coincidencias de todos los grupos; sin ella, solo la pestaña activa.
const visibleIcons = computed(() => {
    const term = normalize(search.value.trim())

    if (!term) {
        return ICON_GROUPS.find((group) => group.name === activeGroup.value).icons
    }

    return ICON_GROUPS.flatMap((group) =>
        group.icons.filter(([, keywords]) =>
            normalize(`${group.name} ${keywords}`).includes(term)
        )
    )
})

async function openPanel() {
    if (open.value) {
        return
    }

    open.value = true

    await nextTick()
    searchInput.value?.focus()
}

function close() {
    open.value = false
    search.value = ''
}

function select(icon) {
    model.value = icon
    close()
}

function clear() {
    model.value = ''
}

// Escape cierra solo el panel, sin propagarse al modal que lo contiene.
function handleKeydown(event) {
    if (event.key === 'Escape' && open.value) {
        event.stopPropagation()
        close()
    }
}

function handleClickOutside(event) {
    if (open.value && !root.value?.contains(event.target)) {
        close()
    }
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside))

onBeforeUnmount(() => document.removeEventListener('mousedown', handleClickOutside))
</script>

<template>
    <div
        ref="root"
        class="icon-picker"
        @keydown="handleKeydown"
    >
        <div class="icon-field">
            <button
                type="button"
                class="icon-preview"
                :class="{ empty: !model }"
                :disabled="disabled"
                aria-label="Elegir ícono"
                @click="open ? close() : openPanel()"
            >
                {{ model || '＋' }}
            </button>

            <input
                :id="id"
                v-model="model"
                type="text"
                class="form-input"
                placeholder="Elige un ícono o pega un emoji"
                maxlength="16"
                autocomplete="off"
                :disabled="disabled"
                @focus="openPanel"
                @click="openPanel"
            />

            <button
                v-if="model && !disabled"
                type="button"
                class="icon-clear"
                aria-label="Quitar ícono"
                @click="clear"
            >
                ×
            </button>
        </div>

        <div
            v-if="open"
            class="icon-panel"
            role="dialog"
            aria-label="Selector de íconos"
        >
            <input
                ref="searchInput"
                v-model="search"
                type="search"
                class="form-input icon-search"
                placeholder="Buscar: mercado, gasolina, mascota..."
            />

            <div
                v-if="!search.trim()"
                class="icon-tabs"
                role="tablist"
            >
                <button
                    v-for="group in ICON_GROUPS"
                    :key="group.name"
                    type="button"
                    role="tab"
                    class="icon-tab"
                    :class="{ active: group.name === activeGroup }"
                    :aria-selected="group.name === activeGroup"
                    @click="activeGroup = group.name"
                >
                    {{ group.name }}
                </button>
            </div>

            <div
                v-if="visibleIcons.length > 0"
                class="icon-grid"
            >
                <button
                    v-for="[icon, keywords] in visibleIcons"
                    :key="icon"
                    type="button"
                    class="icon-option"
                    :class="{ selected: icon === model }"
                    :title="keywords"
                    @click="select(icon)"
                >
                    {{ icon }}
                </button>
            </div>

            <p
                v-else
                class="icon-empty"
            >
                No encontramos íconos para "{{ search }}". También puedes pegar
                cualquier emoji en el campo.
            </p>
        </div>
    </div>
</template>

<style scoped src="../styles/icon-picker.css"></style>
