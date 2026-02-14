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
import { Dialog, DialogFooter } from "@/components/ui/dialog"
import { usePaginatedApi, useNotification } from "@/composables"
import { Plus, Pencil, Trash2, Search } from "lucide-vue-next"
import api from "@/lib/api"
import type { Lote, LoteStatus } from "@/types"

const { items, meta, isLoading, fetch } = usePaginatedApi<Lote>("/lotes")
const { success, error } = useNotification()

const search = ref("")
const currentPage = ref(1)

const deleteDialog = ref(false)
const deleteTarget = ref<Lote | null>(null)
const isDeleting = ref(false)

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

function openDeleteDialog(item: Lote): void {
  deleteTarget.value = item
  deleteDialog.value = true
}

async function confirmDelete(): Promise<void> {
  if (!deleteTarget.value) return

  isDeleting.value = true
  try {
    await api.delete(`/lotes/${deleteTarget.value.id}`)
    success("Excluido!", "Lote removido com sucesso")
    deleteDialog.value = false
    loadData()
  } catch (err) {
    error("Erro", "Nao foi possivel excluir o lote")
  } finally {
    isDeleting.value = false
    deleteTarget.value = null
  }
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value)
}

function getStatusVariant(status: LoteStatus): "success" | "warning" | "destructive" {
  switch (status) {
    case "disponivel":
      return "success"
    case "indisponivel":
      return "warning"
    case "esgotado":
      return "destructive"
    default:
      return "warning"
  }
}

function getStatusLabel(status: LoteStatus): string {
  switch (status) {
    case "disponivel":
      return "Disponivel"
    case "indisponivel":
      return "Indisponivel"
    case "esgotado":
      return "Esgotado"
    default:
      return status
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Lotes</h1>
        <p class="text-muted-foreground">
          Gerencie os lotes de ativos disponiveis
        </p>
      </div>
      <Button as-child>
        <RouterLink :to="{ name: 'lotes.create' }">
          <Plus class="size-4 mr-2" />
          Novo Lote
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
            <TableHead>Ativo</TableHead>
            <TableHead>Quantidade</TableHead>
            <TableHead>Custo Unit.</TableHead>
            <TableHead>Status</TableHead>
            <TableHead class="w-[100px]">Acoes</TableHead>
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
            message="Nenhum lote encontrado"
          />

          <TableRow v-else v-for="item in items" :key="item.id">
            <TableCell class="font-medium">{{ item.codigo }}</TableCell>
            <TableCell>{{ item.tipo_ativo?.nome || "-" }}</TableCell>
            <TableCell>
              {{ item.quantidade_disponivel }} / {{ item.quantidade_total }}
            </TableCell>
            <TableCell>{{ item.custo_unitario ? formatCurrency(item.custo_unitario) : "-" }}</TableCell>
            <TableCell>
              <Badge :variant="getStatusVariant(item.status)">
                {{ getStatusLabel(item.status) }}
              </Badge>
            </TableCell>
            <TableCell>
              <div class="flex items-center gap-1">
                <Button variant="ghost" size="icon-sm" as-child>
                  <RouterLink :to="{ name: 'lotes.edit', params: { id: item.id } }">
                    <Pencil class="size-4" />
                  </RouterLink>
                </Button>
                <Button
                  variant="ghost"
                  size="icon-sm"
                  class="text-destructive hover:text-destructive"
                  @click="openDeleteDialog(item)"
                >
                  <Trash2 class="size-4" />
                </Button>
              </div>
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

    <Dialog v-model:open="deleteDialog" title="Confirmar exclusao">
      <p class="text-muted-foreground">
        Tem certeza que deseja excluir o lote
        <strong>{{ deleteTarget?.codigo }}</strong>?
        Esta acao nao pode ser desfeita.
      </p>
      <DialogFooter>
        <Button variant="outline" @click="deleteDialog = false">Cancelar</Button>
        <Button variant="destructive" :disabled="isDeleting" @click="confirmDelete">
          <Spinner v-if="isDeleting" size="sm" class="mr-2" />
          Excluir
        </Button>
      </DialogFooter>
    </Dialog>
  </div>
</template>
