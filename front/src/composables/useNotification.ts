import { useNotificationStore } from '@/stores/notification'

// Composable para usar notificacoes em componentes
export function useNotification() {
  const store = useNotificationStore()

  return {
    notifications: store.notifications,
    success: store.success,
    error: store.error,
    warning: store.warning,
    info: store.info,
    remove: store.remove,
    clear: store.clear,
  }
}
