// Pasos del recorrido guiado. `target` apunta a un elemento con data-tour="...";
// sin target, la tarjeta aparece centrada sobre la pantalla oscurecida.
// `mobileOnly` muestra el paso solo cuando el menú es hamburguesa.
export const TOUR_STEPS = [
    {
        icon: '👋',
        title: (name) => `¡Bienvenido a FinancePro${name ? `, ${name}` : ''}!`,
        body: 'Te mostramos en un minuto dónde está cada cosa para que empieces a controlar tu dinero. Tus datos son privados: solo tú los ves.',
    },
    {
        target: 'menu-button',
        mobileOnly: true,
        icon: '☰',
        title: 'Tu menú',
        body: 'Toca este botón para abrir el menú y moverte entre las secciones. Te mostramos qué hay en cada una.',
    },
    {
        target: 'cuentas',
        icon: '🏦',
        title: 'Empieza por tus cuentas',
        body: 'Registra aquí tus cuentas bancarias, billeteras como Nequi, efectivo y tarjetas de crédito con su saldo actual. Todo lo demás se mueve desde ellas.',
        tip: 'Es el primer paso: sin una cuenta no puedes registrar ingresos ni gastos.',
    },
    {
        target: 'ingresos',
        icon: '💰',
        title: 'Registra tus ingresos',
        body: 'Anota tu salario, pagos o cualquier dinero que recibas y a qué cuenta llegó. Si cobras cada mes, prográmalo como ingreso recurrente y se registra solo.',
    },
    {
        target: 'gastos',
        icon: '🧾',
        title: 'Controla tus gastos',
        body: 'Cada gasto descuenta del saldo de su cuenta. Escribe la descripción y la app sugiere la categoría automáticamente. Aquí también pagas deudas y aportas a tus metas.',
    },
    {
        target: 'categorias-gasto',
        icon: '🏷️',
        title: 'Organiza por categorías',
        body: 'Ya tienes categorías listas como Mercado, Transporte o Suscripciones. Crea las tuyas con ícono y color, agrupadas en Necesidad, Deseo y Ahorro.',
    },
    {
        target: 'deudas',
        icon: '💳',
        title: 'Lleva tus deudas',
        body: 'Registra préstamos y tarjetas con sus cuotas. Cada pago que hagas desde Gastos reduce el saldo pendiente y verás cuánto te falta.',
    },
    {
        target: 'presupuestos',
        icon: '📊',
        title: 'Tu presupuesto 50/30/20',
        body: 'Mira cómo se reparte tu ingreso: 50% necesidades, 30% deseos y 20% ahorro. También creas aquí tus metas de ahorro y sigues su progreso.',
    },
    {
        target: 'movimientos',
        icon: '🔄',
        title: 'Todos tus movimientos',
        body: 'El historial completo de ingresos y gastos en un solo lugar, con filtros por fecha, cuenta o descripción.',
    },
    {
        target: 'reportes',
        icon: '📈',
        title: 'Reportes y consejos',
        body: 'Analiza en qué se va tu dinero, compara periodos y recibe recomendaciones según tus hábitos.',
    },
    {
        target: 'dashboard',
        icon: '🏠',
        title: 'Tu resumen diario',
        body: 'El dashboard reúne tu saldo total, el balance del mes, tus deudas y los últimos movimientos de un vistazo.',
    },
    {
        target: 'user-menu',
        icon: '👤',
        title: 'Tu perfil',
        body: 'Desde aquí actualizas tus datos y tu foto, y puedes volver a ver este recorrido cuando quieras.',
    },
    {
        icon: '🚀',
        title: '¡Todo listo!',
        body: 'Crea tu primera cuenta y registra tu saldo actual. A partir de ahí, cada ingreso y gasto mantendrá tus finanzas al día.',
        final: true,
    },
]
