import Swal from 'sweetalert2'

export function useSwal() {
  const confirm = (options) => {
    return Swal.fire({
      title: options.title || 'Êtes-vous sûr ?',
      text: options.text || '',
      icon: options.icon || 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: options.confirmButtonText || 'Oui, continuer',
      cancelButtonText: options.cancelButtonText || 'Annuler',
      ...options
    })
  }

  const success = (message, title = 'Succès !') => {
    return Swal.fire({
      title: title,
      text: message,
      icon: 'success',
      confirmButtonColor: '#10b981',
      confirmButtonText: 'OK'
    })
  }

  const error = (message, title = 'Erreur !') => {
    return Swal.fire({
      title: title,
      text: message,
      icon: 'error',
      confirmButtonColor: '#ef4444',
      confirmButtonText: 'OK'
    })
  }

  const toast = (message, icon = 'success') => {
    return Swal.fire({
      toast: true,
      position: 'top-end',
      icon: icon,
      title: message,
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    })
  }

  return {
    confirm,
    success,
    error,
    toast
  }
}
