const NEED_KEYWORDS = [
    'gasolina', 'combustible', 'tanqueo', 'peaje', 'transporte', 'bus', 'metro', 'taxi', 'uber',
    'mercado', 'supermercado', 'comida', 'almuerzo', 'desayuno', 'cena', 'verdura', 'fruta', 'carne', 'aseo',
    'arriendo', 'alquiler', 'renta', 'hipoteca', 'administracion',
    'luz', 'energia', 'agua', 'gas', 'internet', 'telefono', 'celular', 'plan de datos',
    'salud', 'medico', 'medicina', 'droga', 'farmacia', 'eps', 'odontologo', 'examen',
    'colegio', 'matricula', 'universidad', 'pension', 'guarderia', 'utiles',
    'seguro', 'soat', 'impuesto', 'predial', 'mantenimiento', 'reparacion', 'pañal', 'panal',
]

const WANT_KEYWORDS = [
    'netflix', 'spotify', 'disney', 'hbo', 'youtube', 'streaming', 'suscripcion', 'videojuego', 'playstation', 'xbox', 'steam',
    'cine', 'concierto', 'fiesta', 'discoteca', 'bar', 'cerveza', 'licor', 'trago', 'rumba',
    'restaurante', 'domicilio', 'rappi', 'comida rapida', 'pizza', 'hamburguesa', 'helado', 'cafe', 'postre',
    'ropa', 'zapatos', 'tenis', 'accesorio', 'perfume', 'maquillaje', 'salon', 'peluqueria', 'spa',
    'viaje', 'vacaciones', 'hotel', 'paseo', 'vuelo', 'tiquete',
    'regalo', 'juguete', 'hobby', 'gimnasio', 'celular nuevo', 'tecnologia',
]

function normalize(text) {
    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
}

function matches(text, keywords) {
    return keywords.some((keyword) =>
        new RegExp(`(^|[^a-z0-9])${normalize(keyword)}([^a-z0-9]|$)`).test(text)
    )
}

// Devuelve 'need', 'want' o null si la descripción no es concluyente.
export function classifyExpense(description) {
    const text = normalize(description ?? '')

    if (!text.trim()) {
        return null
    }

    // Los deseos son más específicos ("comida rapida", "celular nuevo"), por eso se evalúan primero.
    if (matches(text, WANT_KEYWORDS)) {
        return 'want'
    }

    if (matches(text, NEED_KEYWORDS)) {
        return 'need'
    }

    return null
}

// Palabras clave de las categorías sugeridas, por nombre normalizado de la categoría.
const CATEGORY_KEYWORDS = {
    'mercado': ['mercado', 'supermercado', 'verdura', 'fruta', 'carne', 'aseo', 'pan', 'leche', 'huevos', 'tienda', 'd1', 'ara', 'exito', 'olimpica', 'carulla', 'jumbo'],
    'arriendo/hipoteca': ['arriendo', 'alquiler', 'renta', 'hipoteca', 'administracion', 'cuota de vivienda'],
    'servicios publicos': ['luz', 'energia', 'agua', 'gas', 'internet', 'telefono', 'celular', 'plan de datos', 'acueducto', 'recibo', 'factura de'],
    'transporte': ['gasolina', 'combustible', 'tanqueo', 'peaje', 'transporte', 'bus', 'metro', 'taxi', 'uber', 'didi', 'parqueadero', 'soat', 'pasaje'],
    'salud/medicina': ['salud', 'medico', 'medicina', 'droga', 'drogueria', 'farmacia', 'eps', 'odontologo', 'examen', 'cita medica', 'optica', 'seguro medico'],
    'educacion': ['colegio', 'matricula', 'universidad', 'pension', 'guarderia', 'utiles', 'curso', 'libro', 'semestre'],
    'restaurantes': ['restaurante', 'domicilio', 'rappi', 'comida rapida', 'pizza', 'hamburguesa', 'helado', 'cafe', 'postre', 'sushi', 'corrientazo'],
    'entretenimiento': ['cine', 'concierto', 'fiesta', 'discoteca', 'bar', 'cerveza', 'licor', 'trago', 'rumba', 'videojuego', 'playstation', 'xbox', 'steam', 'boletas', 'teatro', 'partido'],
    'viajes': ['viaje', 'vacaciones', 'hotel', 'paseo', 'vuelo', 'tiquete', 'airbnb', 'hostal'],
    'suscripciones': ['netflix', 'spotify', 'disney', 'hbo', 'youtube', 'streaming', 'suscripcion', 'prime video', 'amazon prime', 'icloud', 'chatgpt', 'membresia'],
    'ropa y calzado': ['ropa', 'zapatos', 'tenis', 'camisa', 'pantalon', 'jean', 'vestido', 'chaqueta', 'calzado'],
    'cuidado personal': ['perfume', 'maquillaje', 'salon', 'peluqueria', 'barberia', 'spa', 'uñas', 'manicure', 'crema', 'corte de pelo', 'gimnasio'],
    'fondo de emergencia': ['emergencia', 'fondo de emergencia', 'colchon'],
    'inversiones': ['inversion', 'invertir', 'acciones', 'cdt', 'fiducia', 'cripto', 'bitcoin', 'etf', 'bolsa'],
    'pago voluntario de deuda': ['abono a deuda', 'abono extra', 'abono a capital', 'prepago', 'pago anticipado'],
    'metas de ahorro': ['ahorro', 'ahorrar', 'alcancia'],
}

// Palabras sueltas del nombre de la categoría: "Ropa y Calzado" → ropa, calzado.
function nameTerms(name) {
    return normalize(name)
        .split(/[\s/,-]+|\by\b/)
        .map((term) => term.trim())
        .filter((term) => term.length >= 3)
}

function longestMatch(text, keywords) {
    return keywords.reduce(
        (best, keyword) =>
            keyword.length > best && matches(text, [keyword])
                ? keyword.length
                : best,
        0
    )
}

// Devuelve la categoría que mejor coincide con la descripción, o null.
// La coincidencia más larga gana: "comida rapida" (Restaurantes) pesa más que "comida".
export function suggestCategory(description, categories) {
    const text = normalize(description ?? '')

    if (!text.trim()) {
        return null
    }

    let best = null
    let bestScore = 0

    for (const category of categories) {
        const keywords = [
            ...nameTerms(category.name),
            ...(CATEGORY_KEYWORDS[normalize(category.name)] ?? []),
        ]

        const score = longestMatch(text, keywords)

        if (score > bestScore) {
            best = category
            bestScore = score
        }
    }

    return best
}
