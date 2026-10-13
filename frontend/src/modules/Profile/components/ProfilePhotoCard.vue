<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },

    busy: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['upload', 'remove', 'invalid'])

const MAX_SIZE_BYTES = 2 * 1024 * 1024

const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp']

const fileInput = ref(null)

const fullName = computed(() =>
    `${props.profile.first_name} ${props.profile.last_name}`.trim()
)

const initials = computed(() =>
    [props.profile.first_name, props.profile.last_name]
        .map((part) => part?.charAt(0) ?? '')
        .join('')
        .toUpperCase()
)

function openPicker() {
    fileInput.value.click()
}

function handleFileChange(event) {
    const [file] = event.target.files

    // Permite volver a elegir el mismo archivo después.
    event.target.value = ''

    if (!file) {
        return
    }

    if (!ALLOWED_TYPES.includes(file.type)) {
        emit('invalid', 'La foto debe ser JPG, PNG o WEBP.')

        return
    }

    if (file.size > MAX_SIZE_BYTES) {
        emit('invalid', 'La foto no puede pesar más de 2 MB.')

        return
    }

    emit('upload', file)
}
</script>

<template>
    <aside class="photo-card">

        <div class="avatar">
            <img
                v-if="profile.photo_url"
                :src="profile.photo_url"
                :alt="`Foto de ${fullName}`"
            />

            <span v-else>
                {{ initials || '?' }}
            </span>
        </div>

        <h2>{{ fullName || 'Tu nombre' }}</h2>

        <p>{{ profile.email }}</p>

        <span
            v-if="profile.employment_type_label"
            class="role-badge"
        >
            {{ profile.employment_type_label }}
        </span>

        <input
            ref="fileInput"
            type="file"
            class="file-input"
            accept="image/jpeg,image/png,image/webp"
            @change="handleFileChange"
        />

        <div class="photo-actions">
            <button
                type="button"
                class="btn btn-secondary"
                :disabled="busy"
                @click="openPicker"
            >
                {{ profile.photo_url ? 'Cambiar foto' : 'Subir foto' }}
            </button>

            <button
                v-if="profile.photo_url"
                type="button"
                class="btn btn-danger-outline"
                :disabled="busy"
                @click="emit('remove')"
            >
                Quitar
            </button>
        </div>

        <small>JPG, PNG o WEBP. Máximo 2 MB.</small>

    </aside>
</template>

<style scoped src="../styles/profile-photo-card.css"></style>
