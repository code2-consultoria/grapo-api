import { ref } from 'vue'
import { defineStore } from 'pinia'

export type NotificationType = 'success' | 'error' | 'warning' | 'info'

export interface Notification {
  id: string
  type: NotificationType
  title: string
  message?: string
  duration?: number
}

export const useNotificationStore = defineStore('notification', () => {
  // Estado
  const notifications = ref<Notification[]>([])

  // Adicionar notificacao
  function add(notification: Omit<Notification, 'id'>): string {
    const id = crypto.randomUUID()
    const duration = notification.duration ?? 5000

    notifications.value.push({ ...notification, id })

    // Auto-remover apos duracao
    if (duration > 0) {
      setTimeout(() => remove(id), duration)
    }

    return id
  }

  // Remover notificacao
  function remove(id: string): void {
    const index = notifications.value.findIndex((n) => n.id === id)
    if (index !== -1) {
      notifications.value.splice(index, 1)
    }
  }

  // Limpar todas
  function clear(): void {
    notifications.value = []
  }

  // Atalhos para tipos comuns
  function success(title: string, message?: string): string {
    return add({ type: 'success', title, message })
  }

  function error(title: string, message?: string): string {
    return add({ type: 'error', title, message, duration: 8000 })
  }

  function warning(title: string, message?: string): string {
    return add({ type: 'warning', title, message })
  }

  function info(title: string, message?: string): string {
    return add({ type: 'info', title, message })
  }

  return {
    // Estado
    notifications,
    // Actions
    add,
    remove,
    clear,
    success,
    error,
    warning,
    info,
  }
})
