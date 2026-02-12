<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"
import { Label } from "@/components/ui/label"

interface Props {
  label?: string
  error?: string
  required?: boolean
  class?: HTMLAttributes["class"]
}

const props = defineProps<Props>()
</script>

<template>
  <div data-slot="form-field" :class="cn('space-y-2', props.class)">
    <Label v-if="label" :required="required">
      {{ label }}
    </Label>
    <slot />
    <p v-if="error" class="text-sm text-destructive">
      {{ error }}
    </p>
  </div>
</template>
