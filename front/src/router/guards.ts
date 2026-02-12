import type { NavigationGuardWithThis, RouteLocationNormalized } from "vue-router"
import { useAuthStore } from "@/stores/auth"

// Guard de autenticacao
export const authGuard: NavigationGuardWithThis<undefined> = async (
  to: RouteLocationNormalized,
  _from: RouteLocationNormalized,
) => {
  const authStore = useAuthStore()

  // Inicializar store se necessario (primeira carga)
  if (!authStore.isInitialized) {
    await authStore.initialize()
  }

  // Verificar se rota requer autenticacao
  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const requiresGuest = to.matched.some((record) => record.meta.requiresGuest)

  // Rota protegida, usuario nao autenticado
  if (requiresAuth && !authStore.isAuthenticated) {
    return {
      name: "login",
      query: { redirect: to.fullPath },
    }
  }

  // Rota de guest (login/registro), usuario ja autenticado
  if (requiresGuest && authStore.isAuthenticated) {
    return { name: "dashboard" }
  }

  return true
}

// Adicionar tipagem para meta das rotas
declare module "vue-router" {
  interface RouteMeta {
    requiresAuth?: boolean
    requiresGuest?: boolean
    title?: string
  }
}
