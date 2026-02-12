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
    data-slot="form-group"
    :class="cn('grid gap-4 sm:grid-cols-2', props.class)"
  >
    <slot />
  </div>
</template>
