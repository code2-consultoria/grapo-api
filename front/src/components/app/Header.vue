<script setup lang="ts">
import { useAuth } from "@/composables"
import UserMenu from "./UserMenu.vue"
import { Bell, Menu } from "lucide-vue-next"
import { Button } from "@/components/ui/button"

const { locador } = useAuth()

const emit = defineEmits<{
  (e: "toggle-sidebar"): void
}>()
</script>

<template>
  <header
    class="flex h-16 items-center justify-between border-b bg-card px-6"
    data-slot="header"
  >
    <!-- Lado esquerdo -->
    <div class="flex items-center gap-4">
      <!-- Botao menu mobile -->
      <Button
        variant="ghost"
        size="icon"
        class="lg:hidden"
        @click="emit('toggle-sidebar')"
      >
        <Menu class="size-5" />
      </Button>

      <!-- Info do locador -->
      <div v-if="locador">
        <p class="text-sm font-medium">{{ locador.nome }}</p>
        <p class="text-xs text-muted-foreground">Locador</p>
      </div>
    </div>

    <!-- Lado direito -->
    <div class="flex items-center gap-2">
      <!-- Notificacoes -->
      <Button variant="ghost" size="icon">
        <Bell class="size-5" />
      </Button>

      <!-- Menu do usuario -->
      <UserMenu />
    </div>
  </header>
</template>
