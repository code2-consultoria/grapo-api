import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { User, Pessoa, LoginResponse, MeResponse } from '@/types'
import api, { setToken, removeToken, hasToken } from '@/lib/api'

export const useAuthStore = defineStore('auth', () => {
  // Estado
  const user = ref<User | null>(null)
  const locador = ref<Pessoa | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const isLoading = ref(false)
  const isInitialized = ref(false)

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.papel === 'admin')
  const userName = computed(() => user.value?.name ?? '')
  const locadorId = computed(() => locador.value?.id ?? null)

  // Actions
  async function login(email: string, password: string): Promise<void> {
    isLoading.value = true
    try {
      const response = await api.post<LoginResponse>('/auth/login', { email, password })
      token.value = response.data.token
      user.value = response.data.user
      setToken(response.data.token)

      // Buscar dados do locador
      await fetchMe()

      // Marcar como inicializado para evitar chamadas duplicadas no guard
      isInitialized.value = true
    } finally {
      isLoading.value = false
    }
  }

  async function logout(): Promise<void> {
    try {
      await api.post('/auth/logout')
    } finally {
      clearAuth()
    }
  }

  async function fetchMe(): Promise<void> {
    if (!hasToken()) return

    try {
      const response = await api.get<MeResponse>('/auth/me')
      user.value = response.data.user
      locador.value = response.data.locador
    } catch {
      clearAuth()
    }
  }

  function clearAuth(): void {
    user.value = null
    locador.value = null
    token.value = null
    removeToken()
  }

  function setAuthData(authToken: string, authUser: User, authLocador: Pessoa | null): void {
    token.value = authToken
    user.value = authUser
    locador.value = authLocador
    setToken(authToken)
    isInitialized.value = true
  }

  // Inicializar estado se houver token
  async function initialize(): Promise<void> {
    if (isInitialized.value) return

    if (hasToken()) {
      token.value = localStorage.getItem('auth_token')
      await fetchMe()
    }

    isInitialized.value = true
  }

  return {
    // Estado
    user,
    locador,
    token,
    isLoading,
    isInitialized,
    // Getters
    isAuthenticated,
    isAdmin,
    userName,
    locadorId,
    // Actions
    login,
    logout,
    fetchMe,
    clearAuth,
    initialize,
    setAuthData,
  }
})
