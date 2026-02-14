import { createRouter, createWebHistory } from "vue-router"
import { authGuard } from "./guards"

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Rota publica - Landing
    {
      path: "/",
      name: "home",
      component: () => import("@/views/HomeView.vue"),
    },

    // Rotas de autenticacao (guest only)
    {
      path: "/auth",
      component: () => import("@/layouts/AuthLayout.vue"),
      meta: { requiresGuest: true },
      children: [
        {
          path: "login",
          name: "login",
          component: () => import("@/views/auth/LoginView.vue"),
          meta: { title: "Entrar" },
        },
        {
          path: "registrar",
          name: "register",
          component: () => import("@/views/auth/RegisterView.vue"),
          meta: { title: "Cadastrar" },
        },
        {
          path: "esqueci-senha",
          name: "forgot-password",
          component: () => import("@/views/auth/ForgotPasswordView.vue"),
          meta: { title: "Recuperar Senha" },
        },
        {
          path: "redefinir-senha/:token",
          name: "reset-password",
          component: () => import("@/views/auth/ResetPasswordView.vue"),
          meta: { title: "Redefinir Senha" },
        },
      ],
    },

    // Rotas autenticadas (app)
    {
      path: "/app",
      component: () => import("@/layouts/AppLayout.vue"),
      meta: { requiresAuth: true },
      children: [
        // Dashboard
        {
          path: "",
          name: "dashboard",
          component: () => import("@/views/app/DashboardView.vue"),
          meta: { title: "Dashboard" },
        },

        // Tipos de Ativos
        {
          path: "tipos-ativos",
          children: [
            {
              path: "",
              name: "tipos-ativos.index",
              component: () => import("@/views/app/tipos-ativos/IndexView.vue"),
              meta: { title: "Tipos de Ativos" },
            },
            {
              path: "criar",
              name: "tipos-ativos.create",
              component: () => import("@/views/app/tipos-ativos/CreateView.vue"),
              meta: { title: "Novo Tipo de Ativo" },
            },
            {
              path: ":id/editar",
              name: "tipos-ativos.edit",
              component: () => import("@/views/app/tipos-ativos/EditView.vue"),
              meta: { title: "Editar Tipo de Ativo" },
            },
          ],
        },

        // Lotes
        {
          path: "lotes",
          children: [
            {
              path: "",
              name: "lotes.index",
              component: () => import("@/views/app/lotes/IndexView.vue"),
              meta: { title: "Lotes" },
            },
            {
              path: "criar",
              name: "lotes.create",
              component: () => import("@/views/app/lotes/CreateView.vue"),
              meta: { title: "Novo Lote" },
            },
            {
              path: ":id/editar",
              name: "lotes.edit",
              component: () => import("@/views/app/lotes/EditView.vue"),
              meta: { title: "Editar Lote" },
            },
          ],
        },

        // Locatarios
        {
          path: "locatarios",
          children: [
            {
              path: "",
              name: "locatarios.index",
              component: () => import("@/views/app/locatarios/IndexView.vue"),
              meta: { title: "Locatarios" },
            },
            {
              path: "criar",
              name: "locatarios.create",
              component: () => import("@/views/app/locatarios/CreateView.vue"),
              meta: { title: "Novo Locatario" },
            },
            {
              path: ":id",
              name: "locatarios.show",
              component: () => import("@/views/app/locatarios/ShowView.vue"),
              meta: { title: "Detalhes do Locatario" },
            },
            {
              path: ":id/editar",
              name: "locatarios.edit",
              component: () => import("@/views/app/locatarios/EditView.vue"),
              meta: { title: "Editar Locatario" },
            },
          ],
        },

        // Contratos
        {
          path: "contratos",
          children: [
            {
              path: "",
              name: "contratos.index",
              component: () => import("@/views/app/contratos/IndexView.vue"),
              meta: { title: "Contratos" },
            },
            {
              path: "criar",
              name: "contratos.create",
              component: () => import("@/views/app/contratos/CreateView.vue"),
              meta: { title: "Novo Contrato" },
            },
            {
              path: ":id",
              name: "contratos.show",
              component: () => import("@/views/app/contratos/ShowView.vue"),
              meta: { title: "Detalhes do Contrato" },
            },
          ],
        },
      ],
    },

    // Rota 404
    {
      path: "/:pathMatch(.*)*",
      name: "not-found",
      component: () => import("@/views/NotFoundView.vue"),
      meta: { title: "Pagina nao encontrada" },
    },
  ],
  scrollBehavior(to) {
    if (to.hash) {
      return {
        el: to.hash,
        behavior: "smooth",
      }
    }
    return { top: 0 }
  },
})

// Registrar guard de autenticacao
router.beforeEach(authGuard)

// Atualizar titulo da pagina
router.afterEach((to) => {
  const appName = import.meta.env.VITE_APP_NAME || "Grapo"
  const pageTitle = to.meta.title
  document.title = pageTitle ? `${pageTitle} | ${appName}` : appName
})

export default router
