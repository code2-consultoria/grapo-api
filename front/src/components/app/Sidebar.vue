<script setup lang="ts">
import { RouterLink, useRoute } from "vue-router"
import { cn } from "@/lib/utils"
import {
  LayoutDashboard,
  Users,
  Package,
  Boxes,
  FileText,
  BarChart3,
} from "lucide-vue-next"

const route = useRoute()

const menuItems = [
  { name: "Dashboard", icon: LayoutDashboard, to: { name: "dashboard" } },
  { name: "Contratos", icon: FileText, to: { name: "contratos.index" } },
  { name: "Locatarios", icon: Users, to: { name: "locatarios.index" } },
  { name: "Meus Ativos", icon: Package, to: { name: "tipos-ativos.index" } },
  { name: "Lotes", icon: Boxes, to: { name: "lotes.index" } },
  { name: "Relatorios", icon: BarChart3, to: { name: "relatorios.financeiro" } },
]

function isActive(itemName: string): boolean {
  const currentRoute = route.name as string

  if (itemName === "Dashboard") {
    return currentRoute === "dashboard"
  }

  const itemRoute = itemName.toLowerCase().replace(" ", "-")
  return currentRoute?.startsWith(itemRoute) || false
}
</script>

<template>
  <aside
    class="flex h-screen w-64 flex-col border-r bg-card"
    data-slot="sidebar"
  >
    <!-- Logo -->
    <div class="flex h-16 items-center gap-2 border-b px-6">
      <img
        src="/icons/icon-96x96.png"
        alt="Grapo"
        class="w-8 h-10"
      />
      <span class="text-xl font-bold uppercase">Grapo</span>
    </div>

    <!-- Menu -->
    <nav class="flex-1 space-y-1 p-4">
      <RouterLink
        v-for="item in menuItems"
        :key="item.name"
        :to="item.to"
        :class="
          cn(
            'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
            isActive(item.name)
              ? 'bg-primary text-primary-foreground'
              : 'text-muted-foreground hover:bg-muted hover:text-foreground',
          )
        "
      >
        <component :is="item.icon" class="size-5" />
        {{ item.name }}
      </RouterLink>
    </nav>

    <!-- Footer -->
    <div class="border-t p-4">
      <p class="text-xs text-muted-foreground text-center">
        &copy; {{ new Date().getFullYear() }} Grapo
      </p>
    </div>
  </aside>
</template>
