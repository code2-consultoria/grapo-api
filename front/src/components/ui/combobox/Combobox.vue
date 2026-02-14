<script setup lang="ts">
import { ref, computed, watch } from "vue"
import {
  ComboboxRoot,
  ComboboxAnchor,
  ComboboxInput,
  ComboboxTrigger,
  ComboboxPortal,
  ComboboxContent,
  ComboboxViewport,
  ComboboxEmpty,
  ComboboxGroup,
  ComboboxItem,
  ComboboxItemIndicator,
} from "reka-ui"
import { cn } from "@/lib/utils"
import { ChevronDown, Check, Plus } from "lucide-vue-next"

export interface ComboboxOption {
  value: string
  label: string
  disabled?: boolean
}

interface Props {
  modelValue?: string
  options: ComboboxOption[]
  placeholder?: string
  searchPlaceholder?: string
  disabled?: boolean
  error?: boolean
  loading?: boolean
  emptyText?: string
  allowCreate?: boolean
  createText?: string
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: "Selecione...",
  searchPlaceholder: "Buscar...",
  emptyText: "Nenhum resultado encontrado",
  allowCreate: false,
  createText: "Criar novo",
})

const emit = defineEmits<{
  (e: "update:modelValue", value: string): void
  (e: "create"): void
}>()

const searchTerm = ref("")
const open = ref(false)

const filteredOptions = computed(() => {
  if (!searchTerm.value) return props.options
  const term = searchTerm.value.toLowerCase()
  return props.options.filter((opt) => opt.label.toLowerCase().includes(term))
})

const selectedLabel = computed(() => {
  const selected = props.options.find((opt) => opt.value === props.modelValue)
  return selected?.label ?? ""
})

function handleSelect(value: string) {
  emit("update:modelValue", value)
  open.value = false
  searchTerm.value = ""
}

function handleCreate() {
  emit("create")
  open.value = false
  searchTerm.value = ""
}

watch(open, (isOpen) => {
  if (!isOpen) {
    searchTerm.value = ""
  }
})
</script>

<template>
  <ComboboxRoot
    v-model:open="open"
    :model-value="modelValue"
    :disabled="disabled"
  >
    <ComboboxAnchor
      :class="
        cn(
          'flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-colors md:text-sm',
          error
            ? 'border-destructive focus-within:ring-destructive text-destructive'
            : 'border-input focus-within:ring-ring',
          disabled && 'cursor-not-allowed opacity-50',
          open && 'ring-1 ring-ring',
        )
      "
    >
      <ComboboxInput
        :placeholder="selectedLabel || placeholder"
        :class="
          cn(
            'flex-1 bg-transparent outline-none placeholder:text-muted-foreground',
            selectedLabel && !open && 'text-foreground',
          )
        "
        :display-value="() => (open ? searchTerm : selectedLabel)"
        @update:model-value="searchTerm = $event"
      />
      <ComboboxTrigger class="ml-2 shrink-0">
        <ChevronDown
          :class="
            cn(
              'size-4 text-muted-foreground transition-transform',
              open && 'rotate-180',
            )
          "
        />
      </ComboboxTrigger>
    </ComboboxAnchor>

    <ComboboxPortal>
      <ComboboxContent
        position="popper"
        :side-offset="4"
        :class="
          cn(
            'relative z-50 max-h-60 min-w-[var(--reka-combobox-trigger-width)] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md',
            'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
            'data-[side=bottom]:slide-in-from-top-2 data-[side=top]:slide-in-from-bottom-2',
          )
        "
      >
        <ComboboxViewport class="p-1">
          <ComboboxEmpty class="py-6 text-center text-sm text-muted-foreground">
            {{ loading ? "Carregando..." : emptyText }}
          </ComboboxEmpty>

          <ComboboxGroup>
            <ComboboxItem
              v-for="option in filteredOptions"
              :key="option.value"
              :value="option.value"
              :disabled="option.disabled"
              :class="
                cn(
                  'relative flex cursor-pointer select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none',
                  'data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground',
                  'data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
                )
              "
              @select="handleSelect(option.value)"
            >
              <ComboboxItemIndicator class="absolute left-2 flex size-4 items-center justify-center">
                <Check class="size-4" />
              </ComboboxItemIndicator>
              {{ option.label }}
            </ComboboxItem>
          </ComboboxGroup>

          <!-- Botao criar novo -->
          <div
            v-if="allowCreate"
            class="border-t mt-1 pt-1"
          >
            <button
              type="button"
              class="relative flex w-full cursor-pointer select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground text-primary"
              @click="handleCreate"
            >
              <Plus class="absolute left-2 size-4" />
              {{ createText }}
            </button>
          </div>
        </ComboboxViewport>
      </ComboboxContent>
    </ComboboxPortal>
  </ComboboxRoot>
</template>
