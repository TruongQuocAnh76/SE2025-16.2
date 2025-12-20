import { ref } from 'vue'

interface ToastOptions {
  message: string
  type?: 'success' | 'error' | 'warning' | 'info'
  duration?: number
}

interface Toast extends ToastOptions {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  duration: number
}

class ToastManager {
  private toasts = ref<Toast[]>([])
  private idCounter = 0

  show(options: ToastOptions) {
    const toast: Toast = {
      id: `toast-${++this.idCounter}`,
      message: options.message,
      type: options.type || 'info',
      duration: options.duration || 3000,
    }

    this.toasts.value.push(toast)

    if (toast.duration > 0) {
      setTimeout(() => {
        this.remove(toast.id)
      }, toast.duration)
    }

    return toast.id
  }

  success(message: string, duration?: number) {
    return this.show({ message, type: 'success', duration })
  }

  error(message: string, duration?: number) {
    return this.show({ message, type: 'error', duration })
  }

  warning(message: string, duration?: number) {
    return this.show({ message, type: 'warning', duration })
  }

  info(message: string, duration?: number) {
    return this.show({ message, type: 'info', duration })
  }

  remove(id: string) {
    const index = this.toasts.value.findIndex((t: Toast) => t.id === id)
    if (index > -1) {
      this.toasts.value.splice(index, 1)
    }
  }

  clear() {
    this.toasts.value = []
  }

  getToasts() {
    return this.toasts
  }
}

export default defineNuxtPlugin(() => {
  const toastManager = new ToastManager()

  return {
    provide: {
      toast: toastManager
    }
  }
})
