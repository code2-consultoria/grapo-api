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
import type { TipoAtivo } from "@/types"

interface TipoAtivoComEstoque extends TipoAtivo {
  quantidade_disponivel?: number
}

const { items, meta, isLoading, fetch } = usePaginatedApi<TipoAtivoComEstoque>("/tipos-ativos")
const { success, error } = useNotification()

const search = ref("")
const currentPage = ref(1)

// Dialog de confirmacao de exclusao
const deleteDialog = ref(false)
const deleteTarget = ref<TipoAtivoComEstoque | null>(null)
const isDeleting = ref(false)

// Carregar dados
onMounted(() => {
  loadData()
})

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

function openDeleteDialog(item: TipoAtivoComEstoque): void {
  deleteTarget.value = item
  deleteDialog.value = true
}

async function confirmDelete(): Promise<void> {
  if (!deleteTarget.value) return

  isDeleting.value = true
  try {
    await api.delete(`/tipos-ativos/${deleteTarget.value.id}`)
    success("Excluido!", "Ativo removido com sucesso")
    deleteDialog.value = false
    loadData()
  } catch (err) {
    error("Erro", "Nao foi possivel excluir o ativo")
  } finally {
    isDeleting.value = false
    deleteTarget.value = null
  }
}

// Formatar valor em reais
function formatCurrency(value: number): string {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value)
}

function getEstoqueVariant(quantidade: number | undefined): "success" | "warning" | "destructive" {
  if (quantidade === undefined || quantidade === 0) return "destructive"
  if (quantidade < 5) return "warning"
  return "success"
}
</script>

<template>
  <div class="space-y-6">
    <!-- Cabecalho -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Meus Ativos</h1>
        <p class="text-muted-foreground">
          Gerencie seus ativos e acompanhe o estoque disponivel
        </p>
      </div>
      <Button as-child>
        <RouterLink :to="{ name: 'tipos-ativos.create' }">
          <Plus class="size-4 mr-2" />
          Novo Ativo
        </RouterLink>
      </Button>
    </div>

    <!-- Filtros -->
    <div class="flex items-center gap-4">
      <div class="relative flex-1 max-w-sm">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
        <Input
          v-model="search"
          placeholder="Buscar por nome..."
          class="pl-9"
          @keyup.enter="handleSearch"
        />
      </div>
      <Button variant="outline" @click="handleSearch">Buscar</Button>
    </div>

    <!-- Tabela -->
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Nome</TableHead>
            <TableHead>Unidade</TableHead>
            <TableHead>Estoque</TableHead>
            <TableHead>Valor Mensal</TableHead>
            <TableHead>Valor Diaria</TableHead>
            <TableHead class="w-[100px]">Acoes</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <!-- Loading -->
          <TableRow v-if="isLoading">
            <TableCell :colspan="6" class="text-center py-8">
              <Spinner />
            </TableCell>
          </TableRow>

          <!-- Empty -->
          <TableEmpty
            v-else-if="items.length === 0"
            :colspan="6"
            message="Nenhum ativo encontrado"
          />

          <!-- Items -->
          <TableRow v-else v-for="item in items" :key="item.id">
            <TableCell>
              <div>
                <p class="font-medium">{{ item.nome }}</p>
                <p v-if="item.descricao" class="text-sm text-muted-foreground">
                  {{ item.descricao }}
                </p>
              </div>
            </TableCell>
            <TableCell>{{ item.unidade_medida }}</TableCell>
            <TableCell>
              <Badge :variant="getEstoqueVariant(item.quantidade_disponivel)">
                {{ item.quantidade_disponivel ?? 0 }} {{ item.unidade_medida }}
              </Badge>
            </TableCell>
            <TableCell>{{ formatCurrency(item.valor_mensal_sugerido) }}</TableCell>
            <TableCell>{{ formatCurrency(item.valor_diaria_sugerido) }}</TableCell>
            <TableCell>
              <div class="flex items-center gap-1">
                <Button variant="ghost" size="icon-sm" as-child>
                  <RouterLink :to="{ name: 'tipos-ativos.edit', params: { id: item.id } }">
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

    <!-- Paginacao -->
    <Pagination
      v-if="meta"
      :current-page="meta.current_page"
      :last-page="meta.last_page"
      :per-page="meta.per_page"
      :total="meta.total"
      @update:current-page="handlePageChange"
    />

    <!-- Dialog de exclusao -->
    <Dialog v-model:open="deleteDialog" title="Confirmar exclusao">
      <p class="text-muted-foreground">
        Tem certeza que deseja excluir o ativo
        <strong>{{ deleteTarget?.nome }}</strong>?
        Esta acao nao pode ser desfeita.
      </p>
      <DialogFooter>
        <Button variant="outline" @click="deleteDialog = false">Cancelar</Button>
        <Button
          variant="destructive"
          :disabled="isDeleting"
          @click="confirmDelete"
        >
          <Spinner v-if="isDeleting" size="sm" class="mr-2" />
          Excluir
        </Button>
      </DialogFooter>
    </Dialog>
  </div>
</template>
