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
import { Plus, Pencil, Trash2, Search, Eye } from "lucide-vue-next"
import api from "@/lib/api"
import type { Pessoa } from "@/types"

const { items, meta, isLoading, fetch } = usePaginatedApi<Pessoa>("/pessoas")
const { success, error } = useNotification()

const search = ref("")
const currentPage = ref(1)

const deleteDialog = ref(false)
const deleteTarget = ref<Pessoa | null>(null)
const isDeleting = ref(false)

onMounted(() => loadData())

async function loadData(): Promise<void> {
  await fetch({
    page: currentPage.value,
    search: search.value || undefined,
    // Filtrar apenas locatarios
    // tipo: 'locatario',
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

function openDeleteDialog(item: Pessoa): void {
  deleteTarget.value = item
  deleteDialog.value = true
}

async function confirmDelete(): Promise<void> {
  if (!deleteTarget.value) return

  isDeleting.value = true
  try {
    await api.delete(`/pessoas/${deleteTarget.value.id}`)
    success("Excluido!", "Locatario removido com sucesso")
    deleteDialog.value = false
    loadData()
  } catch (err) {
    error("Erro", "Nao foi possivel excluir o locatario")
  } finally {
    isDeleting.value = false
    deleteTarget.value = null
  }
}

function formatPhone(phone: string | null): string {
  if (!phone) return "-"
  // Remove tudo que não é número
  const digits = phone.replace(/\D/g, "")
  // Formata (99) 99999-9999 ou (99) 9999-9999
  if (digits.length === 11) {
    return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`
  }
  if (digits.length === 10) {
    return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`
  }
  return phone
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Locatarios</h1>
        <p class="text-muted-foreground">
          Gerencie os locatarios cadastrados
        </p>
      </div>
      <Button as-child>
        <RouterLink :to="{ name: 'locatarios.create' }">
          <Plus class="size-4 mr-2" />
          Novo Locatario
        </RouterLink>
      </Button>
    </div>

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

    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Nome</TableHead>
            <TableHead>E-mail</TableHead>
            <TableHead>Telefone</TableHead>
            <TableHead>Status</TableHead>
            <TableHead class="w-[100px]">Acoes</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="isLoading">
            <TableCell :colspan="5" class="text-center py-8">
              <Spinner />
            </TableCell>
          </TableRow>

          <TableEmpty
            v-else-if="items.length === 0"
            :colspan="5"
            message="Nenhum locatario encontrado"
          />

          <TableRow v-else v-for="item in items" :key="item.id">
            <TableCell>
              <div>
                <RouterLink
                  :to="{ name: 'locatarios.show', params: { id: item.id } }"
                  class="font-medium hover:underline"
                >
                  {{ item.nome }}
                </RouterLink>
                <p v-if="item.tipo_pessoa" class="text-sm text-muted-foreground">
                  {{ item.tipo_pessoa }}
                </p>
              </div>
            </TableCell>
            <TableCell>{{ item.email || "-" }}</TableCell>
            <TableCell>{{ formatPhone(item.telefone) }}</TableCell>
            <TableCell>
              <Badge :variant="item.ativo ? 'success' : 'secondary'">
                {{ item.ativo ? "Ativo" : "Inativo" }}
              </Badge>
            </TableCell>
            <TableCell>
              <div class="flex items-center gap-1">
                <Button variant="ghost" size="icon-sm" as-child>
                  <RouterLink :to="{ name: 'locatarios.show', params: { id: item.id } }">
                    <Eye class="size-4" />
                  </RouterLink>
                </Button>
                <Button variant="ghost" size="icon-sm" as-child>
                  <RouterLink :to="{ name: 'locatarios.edit', params: { id: item.id } }">
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
        Tem certeza que deseja excluir o locatario
        <strong>{{ deleteTarget?.nome }}</strong>?
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
