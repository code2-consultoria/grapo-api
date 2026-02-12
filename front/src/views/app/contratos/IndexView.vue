<script setup lang="ts">
import { ref, onMounted } from "vue"
import { RouterLink } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
  TableEmpty,
} from "@/components/ui/table"
import { Pagination } from "@/components/ui/pagination"
import { Spinner } from "@/components/ui/spinner"
import { usePaginatedApi } from "@/composables"
import { Plus, Eye, Search } from "lucide-vue-next"
import type { Contrato, ContratoStatus } from "@/types"

const { items, meta, isLoading, fetch } = usePaginatedApi<Contrato>("/contratos")

const search = ref("")
const currentPage = ref(1)

onMounted(() => loadData())

async function loadData(): Promise<void> {
  await fetch({
    page: currentPage.value,
    search: search.value || undefined,
  })
}

function handleSearch(): void {
  currentPage.value = 1
  loadData()
}

function handlePageChange(page: number): void {
  currentPage.value = page
  loadData()
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value)
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString("pt-BR")
}

function getStatusVariant(status: ContratoStatus): "default" | "success" | "secondary" | "destructive" {
  switch (status) {
    case "rascunho":
      return "secondary"
    case "ativo":
      return "success"
    case "finalizado":
      return "default"
    case "cancelado":
      return "destructive"
    default:
      return "secondary"
  }
}

function getStatusLabel(status: ContratoStatus): string {
  switch (status) {
    case "rascunho":
      return "Rascunho"
    case "ativo":
      return "Ativo"
    case "finalizado":
      return "Finalizado"
    case "cancelado":
      return "Cancelado"
    default:
      return status
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Contratos</h1>
        <p class="text-muted-foreground">
          Gerencie os contratos de locacao
        </p>
      </div>
      <Button as-child>
        <RouterLink :to="{ name: 'contratos.create' }">
          <Plus class="size-4 mr-2" />
          Novo Contrato
        </RouterLink>
      </Button>
    </div>

    <div class="flex items-center gap-4">
      <div class="relative flex-1 max-w-sm">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
        <Input
          v-model="search"
          placeholder="Buscar por codigo..."
          class="pl-9"
          @keyup.enter="handleSearch"
        />
      </div>
      <Button variant="outline" @click="handleSearch">Buscar</Button>
    </div>

    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Codigo</TableHead>
            <TableHead>Locatario</TableHead>
            <TableHead>Periodo</TableHead>
            <TableHead>Valor Total</TableHead>
            <TableHead>Status</TableHead>
            <TableHead class="w-[80px]">Acoes</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="isLoading">
            <TableCell :colspan="6" class="text-center py-8">
              <Spinner />
            </TableCell>
          </TableRow>

          <TableEmpty
            v-else-if="items.length === 0"
            :colspan="6"
            message="Nenhum contrato encontrado"
          />

          <TableRow v-else v-for="item in items" :key="item.id">
            <TableCell class="font-medium">{{ item.codigo }}</TableCell>
            <TableCell>{{ item.locatario?.nome || "-" }}</TableCell>
            <TableCell>
              {{ formatDate(item.data_inicio) }} - {{ formatDate(item.data_termino) }}
            </TableCell>
            <TableCell>{{ formatCurrency(item.valor_total) }}</TableCell>
            <TableCell>
              <Badge :variant="getStatusVariant(item.status)">
                {{ getStatusLabel(item.status) }}
              </Badge>
            </TableCell>
            <TableCell>
              <Button variant="ghost" size="icon-sm" as-child>
                <RouterLink :to="{ name: 'contratos.show', params: { id: item.id } }">
                  <Eye class="size-4" />
                </RouterLink>
              </Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <Pagination
      v-if="meta"
      :current-page="meta.current_page"
      :last-page="meta.last_page"
      :per-page="meta.per_page"
      :total="meta.total"
      @update:current-page="handlePageChange"
    />
  </div>
</template>
