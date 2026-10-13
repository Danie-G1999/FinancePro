<script setup>
import { computed, onMounted, ref } from 'vue'

import ProfileForm from '../components/ProfileForm.vue'
import ProfilePhotoCard from '../components/ProfilePhotoCard.vue'
import { useProfileStore } from '../stores/profileStore'
import { useAuthStore } from '../../Auth/stores/authStore'
import {
    extractErrorMessage,
    showError,
    showSuccess,
    withLoading,
} from '../../../utils/alerts'

const profileStore = useProfileStore()

const authStore = useAuthStore()

const photoBusy = ref(false)

// Cambia solo al guardar el formulario, para no perder datos sin guardar al subir la foto.
const formVersion = ref(0)

const profile = computed(() => profileStore.profile)

async function handleSave(data) {
    try {
        await withLoading('Guardando perfil...', async () => {
            await profileStore.saveProfile(data)

            // El nombre del usuario se sincroniza con el perfil.
            await authStore.fetchUser()
        })

        formVersion.value++

        showSuccess('Perfil actualizado correctamente.')
    } catch (error) {
        showError(
            extractErrorMessage(error, 'No fue posible guardar el perfil.')
        )
    }
}

async function runPhotoAction(title, task, successMessage, fallback) {
    photoBusy.value = true

    try {
        await withLoading(title, task)

        // El avatar del topbar sale de los datos del usuario autenticado.
        await authStore.fetchUser()

        showSuccess(successMessage)
    } catch (error) {
        showError(extractErrorMessage(error, fallback))
    } finally {
        photoBusy.value = false
    }
}

function handleUpload(file) {
    return runPhotoAction(
        'Subiendo foto...',
        () => profileStore.uploadPhoto(file),
        'Foto actualizada correctamente.',
        'No fue posible subir la foto.'
    )
}

function handleRemove() {
    return runPhotoAction(
        'Eliminando foto...',
        () => profileStore.removePhoto(),
        'Foto eliminada correctamente.',
        'No fue posible eliminar la foto.'
    )
}

onMounted(() => {
    profileStore.fetchProfile()
})
</script>

<template>
    <div class="profile-page">

        <!-- HEADER -->

        <header>
            <h1 class="page-title">
                Mi perfil
            </h1>

            <p class="page-description">
                Administra tu información personal, laboral y de contacto.
            </p>
        </header>


        <!-- LOADING -->

        <div
            v-if="profileStore.loading && !profile"
            class="state-container"
        >
            <div class="loading-spinner"></div>

            <p>
                Cargando perfil...
            </p>
        </div>


        <!-- ERROR -->

        <div
            v-else-if="profileStore.error && !profile"
            class="alert-error"
        >
            {{ profileStore.error }}
        </div>


        <!-- CONTENT -->

        <div
            v-else-if="profile"
            class="profile-layout"
        >

            <ProfilePhotoCard
                :profile="profile"
                :busy="photoBusy"
                @upload="handleUpload"
                @remove="handleRemove"
                @invalid="showError($event, 'Foto no válida')"
            />

            <section class="form-card">

                <div class="form-card-header">
                    <h2>Datos del perfil</h2>

                    <p>
                        Mantén tu información al día.
                    </p>
                </div>

                <div class="form-card-body">

                    <ProfileForm
                        :key="formVersion"
                        :profile="profile"
                        @submit="handleSave"
                    />

                </div>

            </section>

        </div>

    </div>
</template>

<style scoped src="../styles/profile-view.css"></style>
