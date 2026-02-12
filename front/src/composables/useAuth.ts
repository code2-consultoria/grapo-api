import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'

// Composable para autenticacao em componentes
export function useAuth() {
  const store = useAuthStore()
  const router = useRouter()
  const notifications = useNotificationStore()

  const isAuthenticated = computed(() => store.isAuthenticated)
  const user = computed(() => store.user)
  const locador = computed(() => store.locador)
  const isLoading = computed(() => store.isLoading)
  const isAdmin = computed(() => store.isAdmin)
  const userName = computed(() => store.userName)
  const locadorId = computed(() => store.locadorId)

  async function login(email: string, password: string): Promise<boolean> {
    try {
      await store.login(email, password)
      notifications.success('Bem-vindo!', `Ola, ${store.userName}`)
      router.push({ name: 'dashboard' })
      return true
    } catch (err) {
      const error = err as { message?: string }
      notifications.error('Erro no login', error.message || 'Credenciais invalidas')
      return false
    }
  }

  async function logout(): Promise<void> {
    await store.logout()
    notifications.info('Ate logo!', 'Voce foi desconectado')
    router.push({ name: 'login' })
  }

  return {
    // Estado
    isAuthenticated,
    user,
    locador,
    isLoading,
    isAdmin,
    userName,
    locadorId,
    // Actions
    login,
    logout,
  }
}
