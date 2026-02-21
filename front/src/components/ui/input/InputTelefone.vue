<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import type { InputVariants } from "."
import { cn } from "@/lib/utils"
import { inputVariants } from "."

interface Props {
  variant?: InputVariants["variant"]
  modelValue?: string
  class?: HTMLAttributes["class"]
  disabled?: boolean
  placeholder?: string
  error?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: "default",
  placeholder: "(00) 00000-0000",
})

const emit = defineEmits<{
  (e: "update:modelValue", value: string): void
}>()

// Aplica mascara de telefone: (##) #####-####
function aplicarMascara(valor: string): string {
  // Remove tudo que nao e digito
  const digitos = valor.replace(/\D/g, "")

  // Limita a 11 digitos
  const limitado = digitos.slice(0, 11)

  // Aplica mascara progressivamente
  if (limitado.length === 0) return ""
  if (limitado.length <= 2) return `(${limitado}`
  if (limitado.length <= 7) return `(${limitado.slice(0, 2)}) ${limitado.slice(2)}`
  return `(${limitado.slice(0, 2)}) ${limitado.slice(2, 7)}-${limitado.slice(7)}`
}

function handleInput(event: Event): void {
  const target = event.target as HTMLInputElement
  const valorComMascara = aplicarMascara(target.value)

  // Atualiza o input com o valor mascarado
  target.value = valorComMascara

  emit("update:modelValue", valorComMascara)
}
</script>

<template>
  <input
    data-slot="input"
    type="tel"
    inputmode="numeric"
    :value="modelValue"
    :disabled="disabled"
    :placeholder="placeholder"
    :maxlength="15"
    :class="cn(inputVariants({ variant: error ? 'error' : variant }), props.class)"
    @input="handleInput"
  />
</template>
