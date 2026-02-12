<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import type { InputVariants } from "."
import { cn } from "@/lib/utils"
import { inputVariants } from "."

interface Props {
  type?: string
  variant?: InputVariants["variant"]
  modelValue?: string | number
  class?: HTMLAttributes["class"]
  disabled?: boolean
  placeholder?: string
  error?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: "text",
  variant: "default",
})

const emit = defineEmits<{
  (e: "update:modelValue", value: string): void
}>()

function handleInput(event: Event): void {
  const target = event.target as HTMLInputElement
  emit("update:modelValue", target.value)
}
</script>

<template>
  <input
    data-slot="input"
    :type="type"
    :value="modelValue"
    :disabled="disabled"
    :placeholder="placeholder"
    :class="cn(inputVariants({ variant: error ? 'error' : variant }), props.class)"
    @input="handleInput"
  />
</template>
