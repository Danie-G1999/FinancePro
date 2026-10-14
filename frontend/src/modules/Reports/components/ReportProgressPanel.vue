<script setup>
import { formatMoney } from '../../../utils/money'

const props = defineProps({
    title: {
        type: String,
        required: true,
    },

    subtitle: {
        type: String,
        default: '',
    },

    // Etiqueta de lo logrado, p. ej. "Pagado" o "Ahorrado".
    doneLabel: {
        type: String,
        required: true,
    },

    done: {
        type: Number,
        required: true,
    },

    total: {
        type: Number,
        required: true,
    },

    progress: {
        type: Number,
        required: true,
    },

    items: {
        type: Array,
        required: true,
    },

    currency: {
        type: String,
        required: true,
    },

    emptyText: {
        type: String,
        required: true,
    },
})

function money(value) {
    return formatMoney(value, props.currency)
}
</script>

<template>
    <article class="panel">

        <div class="panel-header">
            <div>
                <h3>{{ title }}</h3>

                <p v-if="subtitle">{{ subtitle }}</p>
            </div>
        </div>

        <div
            v-if="items.length > 0"
            class="panel-body"
        >

            <div class="overall">
                <div class="overall-amounts">
                    <span>{{ doneLabel }}</span>

                    <strong>
                        {{ money(done) }}
                        <small>de {{ money(total) }}</small>
                    </strong>
                </div>

                <span class="overall-percent">{{ progress }}%</span>
            </div>

            <div class="progress">
                <div
                    class="progress-bar"
                    :style="{ width: `${progress}%` }"
                ></div>
            </div>

            <ul class="items">
                <li
                    v-for="item in items"
                    :key="item.id"
                    class="item"
                >
                    <div class="item-main">
                        <span class="item-name">{{ item.name }}</span>

                        <span class="item-percent">{{ item.progress }}%</span>
                    </div>

                    <div class="progress small">
                        <div
                            class="progress-bar"
                            :class="{ completed: item.progress >= 100 }"
                            :style="{ width: `${item.progress}%` }"
                        ></div>
                    </div>

                    <small>{{ item.note }}</small>
                </li>
            </ul>

        </div>

        <p
            v-else
            class="panel-empty"
        >
            {{ emptyText }}
        </p>

    </article>
</template>

<style scoped src="../styles/report-panel.css"></style>
<style scoped src="../styles/report-progress.css"></style>
