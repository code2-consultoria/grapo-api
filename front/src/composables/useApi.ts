import { ref } from 'vue'
import api from '@/lib/api'
import type { ApiError, PaginatedResponse, PaginationParams } from '@/types'
import { useNotificationStore } from '@/stores/notification'

// Composable generico para requisicoes API
export function useApi<T>() {
  const data = ref<T | null>(null)
  const isLoading = ref(false)
  const error = ref<ApiError | null>(null)

  const notifications = useNotificationStore()

  async function execute(
    request: () => Promise<T>,
    options: { showError?: boolean } = {},
  ): Promise<T | null> {
    isLoading.value = true
    error.value = null

    try {
      const result = await request()
      data.value = result
      return result
    } catch (err) {
      error.value = err as ApiError

      if (options.showError !== false) {
        notifications.error('Erro na requisicao', error.value.message || 'Ocorreu um erro inesperado')
      }

      return null
    } finally {
      isLoading.value = false
    }
  }

  function reset(): void {
    data.value = null
    error.value = null
    isLoading.value = false
  }

  return {
    data,
    isLoading,
    error,
    execute,
    reset,
  }
}

// Composable especializado para listagens paginadas
export function usePaginatedApi<T>(endpoint: string) {
  const items = ref<T[]>([])
  const meta = ref<PaginatedResponse<T>['meta'] | null>(null)
  const isLoading = ref(false)
  const error = ref<ApiError | null>(null)

  const notifications = useNotificationStore()

  async function fetch(params?: PaginationParams): Promise<void> {
    isLoading.value = true
    error.value = null

    try {
      const response = await api.get<PaginatedResponse<T>>(endpoint, params as Record<string, string | number | boolean | undefined>)
      items.value = response.data
      meta.value = response.meta
    } catch (err) {
      error.value = err as ApiError
      notifications.error('Erro ao carregar dados', error.value.message)
    } finally {
      isLoading.value = false
    }
  }

  function reset(): void {
    items.value = []
    meta.value = null
    error.value = null
    isLoading.value = false
  }

  return {
    items,
    meta,
    isLoading,
    error,
    fetch,
    reset,
  }
}
