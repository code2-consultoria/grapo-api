<script setup lang="ts">
import { computed } from "vue"
import { useAuth } from "@/composables"
import { Dropdown, DropdownItem, DropdownSeparator } from "@/components/ui/dropdown"
import { Button } from "@/components/ui/button"
import { User, LogOut, Settings } from "lucide-vue-next"

const { user, logout } = useAuth()

// Iniciais do nome do usuario
const initials = computed(() => {
  const name = user.value?.name
  if (!name) return "?"
  const parts = name.split(" ")
  if (parts.length === 1) return parts[0]?.charAt(0).toUpperCase() ?? "?"
  const first = parts[0]?.charAt(0) ?? ""
  const last = parts[parts.length - 1]?.charAt(0) ?? ""
  return (first + last).toUpperCase()
})
</script>

<template>
  <Dropdown>
    <template #trigger>
      <Button variant="ghost" size="icon" class="rounded-full">
        <div
          class="size-8 rounded-full bg-primary/20 text-primary flex items-center justify-center text-sm font-medium"
        >
          {{ initials }}
        </div>
      </Button>
    </template>

    <!-- Info do usuario -->
    <div class="px-2 py-1.5">
      <p class="text-sm font-medium">{{ user?.name }}</p>
      <p class="text-xs text-muted-foreground">{{ user?.email }}</p>
    </div>

    <DropdownSeparator />

    <DropdownItem>
      <User class="size-4" />
      Meu perfil
    </DropdownItem>

    <DropdownItem>
      <Settings class="size-4" />
      Configuracoes
    </DropdownItem>

    <DropdownSeparator />

    <DropdownItem class="text-destructive focus:text-destructive" @click="logout">
      <LogOut class="size-4" />
      Sair
    </DropdownItem>
  </Dropdown>
</template>
