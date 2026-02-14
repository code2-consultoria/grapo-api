<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import type { TextareaVariants } from "."
import { cn } from "@/lib/utils"
import { textareaVariants } from "."

interface Props {
  variant?: TextareaVariants["variant"]
  modelValue?: string
  class?: HTMLAttributes["class"]
  disabled?: boolean
  placeholder?: string
  rows?: number | string
  error?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: "default",
  rows: 3,
})

const emit = defineEmits<{
  (e: "update:modelValue", value: string): void
}>()

function handleInput(event: Event): void {
  const target = event.target as HTMLTextAreaElement
  emit("update:modelValue", target.value)
}
</script>

<template>
  <textarea
    data-slot="textarea"
    :value="modelValue"
    :disabled="disabled"
    :placeholder="placeholder"
    :rows="rows"
    :class="cn(textareaVariants({ variant: error ? 'error' : variant }), props.class)"
    @input="handleInput"
  />
</template>
