<script setup lang="ts">
import { ref } from "vue"
import { RouterView } from "vue-router"
import { Sidebar, Header } from "@/components/app"
import { ToastContainer } from "@/components/ui/toast"

const isSidebarOpen = ref(false)

function toggleSidebar(): void {
  isSidebarOpen.value = !isSidebarOpen.value
}
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- Toast notifications -->
    <ToastContainer />

    <div class="flex">
      <!-- Sidebar - desktop -->
      <Sidebar class="hidden lg:flex" />

      <!-- Sidebar - mobile (overlay) -->
      <Teleport to="body">
        <Transition name="sidebar-overlay">
          <div
            v-if="isSidebarOpen"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="isSidebarOpen = false"
          />
        </Transition>
        <Transition name="sidebar">
          <Sidebar
            v-if="isSidebarOpen"
            class="fixed inset-y-0 left-0 z-50 lg:hidden"
          />
        </Transition>
      </Teleport>

      <!-- Conteudo principal -->
      <div class="flex-1 flex flex-col min-h-screen">
        <Header @toggle-sidebar="toggleSidebar" />

        <main class="flex-1 p-6">
          <RouterView />
        </main>
      </div>
    </div>
  </div>
</template>

<style scoped>
.sidebar-overlay-enter-active,
.sidebar-overlay-leave-active {
  transition: opacity 0.3s ease;
}

.sidebar-overlay-enter-from,
.sidebar-overlay-leave-to {
  opacity: 0;
}

.sidebar-enter-active,
.sidebar-leave-active {
  transition: transform 0.3s ease;
}

.sidebar-enter-from,
.sidebar-leave-to {
  transform: translateX(-100%);
}
</style>
