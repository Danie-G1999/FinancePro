<script setup>
defineProps({
    insights: {
        type: Array,
        required: true,
    },
})

const ICONS = {
    positive: '✓',
    warning: '!',
    danger: '✕',
    info: 'i',
}
</script>

<template>
    <article class="panel">

        <div class="panel-header">
            <div>
                <h3>Lo más importante</h3>

                <p>Un resumen claro de cómo van tus finanzas.</p>
            </div>
        </div>

        <ul class="insights">
            <li
                v-for="(insight, index) in insights"
                :key="index"
                class="insight"
                :class="insight.type"
            >
                <span class="insight-icon" aria-hidden="true">
                    {{ ICONS[insight.type] }}
                </span>

                <span>{{ insight.message }}</span>
            </li>
        </ul>

    </article>
</template>

<style scoped src="../styles/report-panel.css"></style>
<style scoped src="../styles/report-insights.css"></style>
