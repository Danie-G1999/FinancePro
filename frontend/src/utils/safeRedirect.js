// Solo rutas internas: evita redirecciones abiertas a otros sitios (?redirect=//malo.com).
export function safeRedirect(path) {
    return typeof path === 'string' && path.startsWith('/') && !path.startsWith('//')
        ? path
        : null
}
