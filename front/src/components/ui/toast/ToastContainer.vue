<script setup lang="ts">
import { useNotificationStore } from "@/stores/notification"
import Toast from "./Toast.vue"

const store = useNotificationStore()
</script>

<template>
  <Teleport to="body">
    <div
      class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none"
    >
      <TransitionGroup name="toast">
        <Toast
          v-for="notification in store.notifications"
          :key="notification.id"
          :id="notification.id"
          :type="notification.type"
          :title="notification.title"
          :message="notification.message"
          class="pointer-events-auto"
          @close="store.remove"
        />
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}
</style>
