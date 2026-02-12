<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"
import { ChevronDown } from "lucide-vue-next"

interface Option {
  value: string
  label: string
  disabled?: boolean
}

interface Props {
  modelValue?: string
  options: Option[]
  placeholder?: string
  disabled?: boolean
  error?: boolean
  class?: HTMLAttributes["class"]
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: "Selecione...",
})

const emit = defineEmits<{
  (e: "update:modelValue", value: string): void
}>()

function handleChange(event: Event): void {
  const target = event.target as HTMLSelectElement
  emit("update:modelValue", target.value)
}
</script>

<template>
  <div class="relative" data-slot="select">
    <select
      :value="modelValue"
      :disabled="disabled"
      :class="
        cn(
          'flex h-9 w-full appearance-none rounded-md border bg-transparent px-3 py-1 pr-8 text-base shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
          error
            ? 'border-destructive focus-visible:ring-destructive text-destructive'
            : 'border-input focus-visible:ring-ring',
          props.class,
        )
      "
      @change="handleChange"
    >
      <option value="" disabled :selected="!modelValue">{{ placeholder }}</option>
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
        :disabled="option.disabled"
      >
        {{ option.label }}
      </option>
    </select>
    <ChevronDown
      class="absolute right-2 top-1/2 -translate-y-1/2 size-4 text-muted-foreground pointer-events-none"
    />
  </div>
</template>
