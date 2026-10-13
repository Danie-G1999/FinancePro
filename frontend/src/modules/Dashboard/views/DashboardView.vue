<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../../Auth/stores/authStore'

const authStore = useAuthStore()

const formattedDate = computed(() => {
    return new Intl.DateTimeFormat('es-CO', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date())
})
</script>

<template>
    <section class="dashboard">
        <div class="welcome">
            <div>
                <h2>
                    Hola, {{ authStore.user?.name ?? 'Juan' }} 👋
                </h2>

                <p>
                    Este es el resumen de tus finanzas personales.
                </p>
            </div>

            <span>
                {{ formattedDate }}
            </span>
        </div>

        <div class="summary-grid">
            <article class="summary-card">
                <div class="card-label">
                    <span>Saldo total</span>

                    <div class="card-icon">
                        $
                    </div>
                </div>

                <strong>$0</strong>

                <small>
                    Disponible actualmente
                </small>
            </article>

            <article class="summary-card">
                <div class="card-label">
                    <span>Ingresos</span>

                    <div class="card-icon">
                        ↑
                    </div>
                </div>

                <strong>$0</strong>

                <small>
                    Este mes
                </small>
            </article>

            <article class="summary-card">
                <div class="card-label">
                    <span>Gastos</span>

                    <div class="card-icon">
                        ↓
                    </div>
                </div>

                <strong>$0</strong>

                <small>
                    Este mes
                </small>
            </article>

            <article class="summary-card">
                <div class="card-label">
                    <span>Deudas</span>

                    <div class="card-icon">
                        #
                    </div>
                </div>

                <strong>$0</strong>

                <small>
                    Pendiente por pagar
                </small>
            </article>
        </div>

        <div class="dashboard-grid">
            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3>Flujo de dinero</h3>
                        <span>Ingresos vs. gastos</span>
                    </div>

                    <button type="button">
                        Este mes
                    </button>
                </div>

                <div class="empty-chart">
                    <div class="empty-icon">
                        ↗
                    </div>

                    <strong>
                        Aún no hay movimientos
                    </strong>

                    <span>
                        Cuando registres ingresos y gastos,
                        aparecerán aquí.
                    </span>
                </div>
            </article>

            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3>Resumen financiero</h3>
                        <span>Estado actual</span>
                    </div>
                </div>

                <div class="financial-summary">
                    <div>
                        <span>Ingresos</span>
                        <strong>$0</strong>
                    </div>

                    <div>
                        <span>Gastos</span>
                        <strong>$0</strong>
                    </div>

                    <div>
                        <span>Balance</span>
                        <strong>$0</strong>
                    </div>
                </div>
            </article>
        </div>

        <article class="panel transactions-panel">
            <div class="panel-header">
                <div>
                    <h3>Últimos movimientos</h3>
                    <span>Actividad reciente</span>
                </div>

                <button type="button">
                    Ver todos
                </button>
            </div>

            <div class="empty-transactions">
                <div class="empty-icon">
                    +
                </div>

                <strong>
                    No hay movimientos registrados
                </strong>

                <span>
                    Empieza agregando tu primer ingreso o gasto.
                </span>
            </div>
        </article>
    </section>
</template>

<style scoped>
.dashboard {
    max-width: 1600px;
    margin: 0 auto;
}

.welcome {
    margin-bottom: 24px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
}

.welcome h2 {
    margin: 0;
    color: #111827;
    font-size: 22px;
    font-weight: 700;
}

.welcome p {
    margin: 6px 0 0;
    color: #6b7280;
    font-size: 13px;
}

.welcome > span {
    color: #9ca3af;
    font-size: 11px;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.summary-card {
    padding: 20px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
}

.card-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card-label > span {
    color: #6b7280;
    font-size: 12px;
}

.card-icon {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 14px;
    font-weight: 700;
}

.summary-card > strong {
    display: block;
    margin-top: 17px;
    color: #111827;
    font-size: 24px;
    font-weight: 700;
}

.summary-card small {
    display: block;
    margin-top: 6px;
    color: #9ca3af;
    font-size: 10px;
}

.dashboard-grid {
    margin-top: 16px;
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 16px;
}

.panel {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
}

.panel-header {
    padding: 19px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f0f1f3;
}

.panel-header h3 {
    margin: 0;
    color: #111827;
    font-size: 14px;
    font-weight: 650;
}

.panel-header span {
    display: block;
    margin-top: 4px;
    color: #9ca3af;
    font-size: 10px;
}

.panel-header button {
    padding: 7px 11px;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    background: #ffffff;
    color: #6b7280;
    font-size: 10px;
    cursor: pointer;
}

.empty-chart {
    min-height: 260px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.empty-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #f3f4f6;
    color: #9ca3af;
    font-size: 18px;
}

.empty-chart strong,
.empty-transactions strong {
    margin-top: 12px;
    color: #374151;
    font-size: 12px;
}

.empty-chart span,
.empty-transactions span {
    max-width: 280px;
    margin-top: 5px;
    color: #9ca3af;
    font-size: 10px;
    line-height: 1.5;
}

.financial-summary {
    padding: 10px 20px 20px;
}

.financial-summary > div {
    padding: 15px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f3f4f6;
}

.financial-summary > div:last-child {
    border-bottom: 0;
}

.financial-summary span {
    color: #6b7280;
    font-size: 11px;
}

.financial-summary strong {
    color: #111827;
    font-size: 13px;
}

.transactions-panel {
    margin-top: 16px;
}

.empty-transactions {
    min-height: 180px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

@media (max-width: 1100px) {
    .summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .app-content {
        padding: 18px;
    }

    .summary-grid {
        grid-template-columns: 1fr;
    }

    .welcome {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>