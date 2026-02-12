<script setup lang="ts">
import { computed } from "vue"
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from "lucide-vue-next"

interface Props {
  currentPage: number
  lastPage: number
  perPage?: number
  total?: number
  class?: HTMLAttributes["class"]
}

const props = withDefaults(defineProps<Props>(), {
  perPage: 15,
})

const emit = defineEmits<{
  (e: "update:currentPage", page: number): void
}>()

// Gerar array de paginas visiveis
const visiblePages = computed(() => {
  const pages: (number | "...")[] = []
  const current = props.currentPage
  const last = props.lastPage

  if (last <= 7) {
    // Mostrar todas as paginas
    for (let i = 1; i <= last; i++) {
      pages.push(i)
    }
  } else {
    // Mostrar com elipses
    if (current <= 3) {
      pages.push(1, 2, 3, 4, "...", last)
    } else if (current >= last - 2) {
      pages.push(1, "...", last - 3, last - 2, last - 1, last)
    } else {
      pages.push(1, "...", current - 1, current, current + 1, "...", last)
    }
  }

  return pages
})

const from = computed(() => {
  if (!props.total) return 0
  return (props.currentPage - 1) * props.perPage + 1
})

const to = computed(() => {
  if (!props.total) return 0
  return Math.min(props.currentPage * props.perPage, props.total)
})

function goToPage(page: number): void {
  if (page >= 1 && page <= props.lastPage) {
    emit("update:currentPage", page)
  }
}
</script>

<template>
  <div
    data-slot="pagination"
    :class="cn('flex items-center justify-between gap-4', props.class)"
  >
    <!-- Info de registros -->
    <div v-if="total" class="text-sm text-muted-foreground">
      Mostrando {{ from }} a {{ to }} de {{ total }} registros
    </div>
    <div v-else />

    <!-- Controles de paginacao -->
    <div class="flex items-center gap-1">
      <!-- Primeira pagina -->
      <Button
        variant="outline"
        size="icon-sm"
        :disabled="currentPage === 1"
        @click="goToPage(1)"
      >
        <ChevronsLeft class="size-4" />
      </Button>

      <!-- Pagina anterior -->
      <Button
        variant="outline"
        size="icon-sm"
        :disabled="currentPage === 1"
        @click="goToPage(currentPage - 1)"
      >
        <ChevronLeft class="size-4" />
      </Button>

      <!-- Paginas -->
      <template v-for="page in visiblePages" :key="page">
        <Button
          v-if="page !== '...'"
          :variant="page === currentPage ? 'default' : 'outline'"
          size="icon-sm"
          @click="goToPage(page)"
        >
          {{ page }}
        </Button>
        <span v-else class="px-2 text-muted-foreground">...</span>
      </template>

      <!-- Proxima pagina -->
      <Button
        variant="outline"
        size="icon-sm"
        :disabled="currentPage === lastPage"
        @click="goToPage(currentPage + 1)"
      >
        <ChevronRight class="size-4" />
      </Button>

      <!-- Ultima pagina -->
      <Button
        variant="outline"
        size="icon-sm"
        :disabled="currentPage === lastPage"
        @click="goToPage(lastPage)"
      >
        <ChevronsRight class="size-4" />
      </Button>
    </div>
  </div>
</template>
