<script setup lang="ts">
import type { LabelProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { Label } from "reka-ui"
import { cn } from "@/lib/utils"

interface Props extends LabelProps {
  class?: HTMLAttributes["class"]
  required?: boolean
}

const props = defineProps<Props>()
</script>

<template>
  <Label
    data-slot="label"
    :class="
      cn(
        'flex items-center gap-1 text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
        props.class,
      )
    "
    v-bind="props"
  >
    <slot />
    <span v-if="required" class="text-destructive">*</span>
  </Label>
</template>
