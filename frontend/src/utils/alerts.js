import Swal from 'sweetalert2'

const PRIMARY_COLOR = '#2563eb'

export function extractErrorMessage(error, fallback) {
    const response = error.response?.data

    return (
        Object.values(response?.errors ?? {})[0]?.[0] ??
        response?.message ??
        fallback
    )
}

export async function withLoading(title, task) {
    Swal.fire({
        title,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    })

    try {
        return await task()
    } finally {
        Swal.close()
    }
}

export function showSuccess(title) {
    return Swal.fire({
        icon: 'success',
        title,
        timer: 1800,
        timerProgressBar: true,
        showConfirmButton: false,
    })
}

export function showError(text, title = 'Ocurrió un error') {
    return Swal.fire({
        icon: 'error',
        title,
        text,
        confirmButtonText: 'Entendido',
        confirmButtonColor: PRIMARY_COLOR,
    })
}

export async function confirmAction({
    title,
    text,
    confirmText = 'Confirmar',
    danger = false,
}) {
    const result = await Swal.fire({
        icon: 'warning',
        title,
        text,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: danger ? '#dc2626' : PRIMARY_COLOR,
        reverseButtons: true,
    })

    return result.isConfirmed
}
