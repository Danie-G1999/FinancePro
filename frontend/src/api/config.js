// URL del backend Laravel, sin "/api" ni barra final.
// Local: frontend/.env.development · Producción: variable VITE_API_URL en Vercel.
const configured = (import.meta.env.VITE_API_URL ?? '').trim().replace(/\/+$/, '')

if (!configured) {
    // Sin esto el build de producción apuntaría a ningún lado y fallaría en silencio.
    console.error(
        'Falta VITE_API_URL: define la URL del backend (p. ej. https://api.tudominio.com) ' +
        'en las variables de entorno de Vercel y vuelve a desplegar.'
    )
}

export const API_URL = configured
