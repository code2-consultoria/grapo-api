<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

interface Props {
  class?: HTMLAttributes["class"]
}

const props = defineProps<Props>()
</script>

<template>
  <div
    data-slot="form-actions"
    :class="cn('flex items-center justify-end gap-3 pt-4', props.class)"
  >
    <slot />
  </div>
</template>
