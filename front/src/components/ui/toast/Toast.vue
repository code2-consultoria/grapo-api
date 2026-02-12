<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import type { ToastVariants } from "."
import { cn } from "@/lib/utils"
import { toastVariants } from "."
import { X, CheckCircle2, AlertCircle, AlertTriangle, Info } from "lucide-vue-next"

interface Props {
  id: string
  type: "success" | "error" | "warning" | "info"
  title: string
  message?: string
  class?: HTMLAttributes["class"]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: "close", id: string): void
}>()

const icons = {
  success: CheckCircle2,
  error: AlertCircle,
  warning: AlertTriangle,
  info: Info,
}
</script>

<template>
  <div data-slot="toast" :class="cn(toastVariants({ variant: type }), props.class)">
    <div class="flex items-start gap-3">
      <component :is="icons[type]" class="size-5 shrink-0 mt-0.5" />
      <div class="flex-1 min-w-0">
        <p class="font-medium">{{ title }}</p>
        <p v-if="message" class="text-sm opacity-90 mt-0.5">{{ message }}</p>
      </div>
      <button
        type="button"
        class="shrink-0 opacity-70 hover:opacity-100 transition-opacity"
        @click="emit('close', id)"
      >
        <X class="size-4" />
      </button>
    </div>
  </div>
</template>
