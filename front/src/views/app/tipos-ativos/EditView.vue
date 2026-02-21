<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import { useRouter, useRoute } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Dialog, DialogFooter } from "@/components/ui/dialog"
import { FormField, FormGroup, FormActions } from "@/components/forms"
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
  TableEmpty,
} from "@/components/ui/table"
import { Spinner } from "@/components/ui/spinner"
import { useForm, useNotification } from "@/composables"
import { ArrowLeft, Plus, Package, Pencil, Trash2 } from "lucide-vue-next"
import api from "@/lib/api"
import type { TipoAtivo, TipoAtivoForm, ApiResponse, Lote, LoteForm, LoteStatus, PaginatedResponse } from "@/types"

const router = useRouter()
const route = useRoute()
const { success, error } = useNotification()

const isLoading = ref(true)
const tipoAtivo = ref<TipoAtivo | null>(null)

// Estado dos lotes
const lotes = ref<Lote[]>([])
const isLoadingLotes = ref(false)
const showLoteDialog = ref(false)
const editingLote = ref<Lote | null>(null)
const isSubmittingLote = ref(false)
const showDeleteLoteDialog = ref(false)
const deletingLoteId = ref<string | null>(null)
const isDeletingLote = ref(false)

// Formulario de lote
const loteForm = ref<LoteForm>({
  codigo: "",
  tipo_ativo_id: "",
  quantidade_total: "",
  fornecedor: "",
  valor_total: "",
  valor_frete: "",
  forma_pagamento: "",
  nf: "",
  data_aquisicao: "",
  status: "disponivel",
})

const form = useForm<TipoAtivoForm>({
  initialValues: {
    nome: "",
    descricao: "",
    unidade_medida: "",
    valor_mensal_sugerido: "",
  },
  validate(values) {
    const errors: Partial<Record<keyof TipoAtivoForm, string>> = {}

    if (!values.nome) {
      errors.nome = "Nome e obrigatorio"
    }

    if (!values.unidade_medida) {
      errors.unidade_medida = "Unidade de medida e obrigatoria"
    }

    if (!values.valor_mensal_sugerido) {
      errors.valor_mensal_sugerido = "Valor mensal e obrigatorio"
    } else if (Number(values.valor_mensal_sugerido) <= 0) {
      errors.valor_mensal_sugerido = "Valor deve ser maior que zero"
    }

    return errors
  },
  async onSubmit(values) {
    await api.put(`/tipos-ativos/${route.params.id}`, {
      ...values,
      valor_mensal_sugerido: Number(values.valor_mensal_sugerido),
    })
    success("Sucesso!", "Tipo de ativo atualizado com sucesso")
    router.push({ name: "tipos-ativos.index" })
  },
})

// Computed
const totalUnidades = computed(() => {
  return lotes.value.reduce((sum, lote) => sum + lote.quantidade_total, 0)
})

const totalDisponivel = computed(() => {
  return lotes.value.reduce((sum, lote) => sum + lote.quantidade_disponivel, 0)
})

// Carregar dados
onMounted(async () => {
  try {
    const response = await api.get<ApiResponse<TipoAtivo>>(
      `/tipos-ativos/${route.params.id}`,
    )
    tipoAtivo.value = response.data

    // Preencher formulario
    form.setValues({
      nome: response.data.nome,
      descricao: response.data.descricao || "",
      unidade_medida: response.data.unidade_medida,
      valor_mensal_sugerido: String(response.data.valor_mensal_sugerido),
    })

    // Carregar lotes
    await loadLotes()
  } catch (err) {
    error("Erro", "Tipo de ativo nao encontrado")
    router.push({ name: "tipos-ativos.index" })
  } finally {
    isLoading.value = false
  }
})

async function loadLotes(): Promise<void> {
  isLoadingLotes.value = true
  try {
    const tipoAtivoId = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id
    const response = await api.get<PaginatedResponse<Lote>>("/lotes", {
      tipo_ativo_id: tipoAtivoId,
      per_page: 100,
    })
    lotes.value = response.data
  } catch {
    // Silently fail
  } finally {
    isLoadingLotes.value = false
  }
}

// Handlers de lote
function openAddLoteDialog(): void {
  editingLote.value = null
  loteForm.value = {
    codigo: "",
    tipo_ativo_id: route.params.id as string,
    quantidade_total: "",
    fornecedor: "",
    valor_total: "",
    valor_frete: "",
    forma_pagamento: "",
    nf: "",
    data_aquisicao: new Date().toISOString().split('T')[0],
    status: "disponivel",
  }
  showLoteDialog.value = true
}

function openEditLoteDialog(lote: Lote): void {
  editingLote.value = lote
  loteForm.value = {
    codigo: lote.codigo,
    tipo_ativo_id: route.params.id as string,
    quantidade_total: String(lote.quantidade_total),
    fornecedor: lote.fornecedor || "",
    valor_total: lote.valor_total ? String(lote.valor_total) : "",
    valor_frete: lote.valor_frete ? String(lote.valor_frete) : "",
    forma_pagamento: lote.forma_pagamento || "",
    nf: lote.nf || "",
    data_aquisicao: lote.data_aquisicao || "",
    status: lote.status,
  }
  showLoteDialog.value = true
}

async function submitLote(): Promise<void> {
  isSubmittingLote.value = true
  try {
    const payload = {
      ...loteForm.value,
      quantidade_total: Number(loteForm.value.quantidade_total),
      valor_total: loteForm.value.valor_total ? Number(loteForm.value.valor_total) : null,
      valor_frete: loteForm.value.valor_frete ? Number(loteForm.value.valor_frete) : null,
    }

    if (editingLote.value) {
      await api.put(`/lotes/${editingLote.value.id}`, payload)
      success("Sucesso!", "Lote atualizado")
    } else {
      await api.post("/lotes", payload)
      success("Sucesso!", "Lote criado")
    }

    showLoteDialog.value = false
    await loadLotes()
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    error("Erro", apiError.message || "Erro ao salvar lote")
  } finally {
    isSubmittingLote.value = false
  }
}

function openDeleteLoteDialog(loteId: string): void {
  deletingLoteId.value = loteId
  showDeleteLoteDialog.value = true
}

async function deleteLote(): Promise<void> {
  if (!deletingLoteId.value) return

  isDeletingLote.value = true
  try {
    await api.delete(`/lotes/${deletingLoteId.value}`)
    success("Sucesso!", "Lote removido")
    showDeleteLoteDialog.value = false
    await loadLotes()
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    error("Erro", apiError.message || "Erro ao remover lote")
  } finally {
    isDeletingLote.value = false
  }
}

// Formatadores
function formatCurrency(value: number | null): string {
  if (value === null) return "-"
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value)
}

function formatDate(date: string | null): string {
  if (!date) return "-"
  return new Date(date).toLocaleDateString("pt-BR")
}

function getStatusVariant(status: LoteStatus): "default" | "success" | "secondary" | "destructive" {
  switch (status) {
    case "disponivel":
      return "success"
    case "indisponivel":
      return "secondary"
    case "esgotado":
      return "destructive"
    default:
      return "default"
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
    <!-- Cabecalho -->
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" @click="router.back()">
        <ArrowLeft class="size-5" />
      </Button>
      <div>
        <h1 class="text-2xl font-bold">Editar Tipo de Ativo</h1>
        <p class="text-muted-foreground">
          Atualize os dados do tipo de ativo
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <Spinner size="lg" />
    </div>

    <template v-else>
      <!-- Formulario -->
      <Card class="max-w-2xl">
        <CardHeader>
          <CardTitle>Dados do Tipo de Ativo</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit="form.handleSubmit" class="space-y-4">
            <FormField label="Nome" :error="form.getError('nome')" required>
              <Input
                v-model="form.values.nome"
                placeholder="Ex: Cadeira plastica"
                :error="form.hasError('nome')"
              />
            </FormField>

            <FormField label="Descricao" :error="form.getError('descricao')">
              <Input
                v-model="form.values.descricao"
                placeholder="Descricao opcional"
              />
            </FormField>

            <FormGroup>
              <FormField
                label="Unidade de Medida"
                :error="form.getError('unidade_medida')"
                required
              >
                <Input
                  v-model="form.values.unidade_medida"
                  placeholder="Ex: unidade, m², kg"
                  :error="form.hasError('unidade_medida')"
                />
              </FormField>

              <FormField
                label="Valor Mensal Sugerido (R$)"
                :error="form.getError('valor_mensal_sugerido')"
                required
              >
                <Input
                  type="number"
                  step="0.01"
                  min="0"
                  v-model="form.values.valor_mensal_sugerido"
                  placeholder="0,00"
                  :error="form.hasError('valor_mensal_sugerido')"
                />
              </FormField>
            </FormGroup>

            <FormActions>
              <Button
                type="button"
                variant="outline"
                @click="router.push({ name: 'tipos-ativos.index' })"
              >
                Cancelar
              </Button>
              <Button type="submit" :disabled="form.isSubmitting.value">
                <Spinner v-if="form.isSubmitting.value" size="sm" class="mr-2" />
                Salvar
              </Button>
            </FormActions>
          </form>
        </CardContent>
      </Card>

      <!-- Secao de Lotes -->
      <Card>
        <CardHeader class="flex flex-row items-center justify-between">
          <div>
            <CardTitle class="flex items-center gap-2">
              <Package class="size-5" />
              Lotes (Estoque)
            </CardTitle>
            <CardDescription>
              Gerencie os lotes de estoque deste tipo de ativo
            </CardDescription>
          </div>
          <Button size="sm" @click="openAddLoteDialog">
            <Plus class="size-4 mr-2" />
            Novo Lote
          </Button>
        </CardHeader>
        <CardContent>
          <!-- Resumo -->
          <div class="grid gap-4 md:grid-cols-3 mb-6">
            <div class="rounded-lg bg-muted p-4">
              <p class="text-sm text-muted-foreground">Total de Lotes</p>
              <p class="text-2xl font-bold">{{ lotes.length }}</p>
            </div>
            <div class="rounded-lg bg-muted p-4">
              <p class="text-sm text-muted-foreground">Total de Unidades</p>
              <p class="text-2xl font-bold">{{ totalUnidades }}</p>
            </div>
            <div class="rounded-lg bg-muted p-4">
              <p class="text-sm text-muted-foreground">Unidades Disponiveis</p>
              <p class="text-2xl font-bold text-green-600">{{ totalDisponivel }}</p>
            </div>
          </div>

          <!-- Tabela de Lotes -->
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Codigo</TableHead>
                  <TableHead>Qtd Total</TableHead>
                  <TableHead>Disponivel</TableHead>
                  <TableHead>Custo Unit.</TableHead>
                  <TableHead>Data Aquisicao</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead class="w-[100px]">Acoes</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableEmpty
                  v-if="lotes.length === 0"
                  :colspan="7"
                  message="Nenhum lote cadastrado para este ativo"
                />
                <TableRow v-else v-for="lote in lotes" :key="lote.id">
                  <TableCell class="font-medium">{{ lote.codigo }}</TableCell>
                  <TableCell>{{ lote.quantidade_total }}</TableCell>
                  <TableCell>
                    <span :class="{ 'text-destructive font-medium': lote.quantidade_disponivel === 0 }">
                      {{ lote.quantidade_disponivel }}
                    </span>
                  </TableCell>
                  <TableCell>{{ formatCurrency(lote.custo_unitario) }}</TableCell>
                  <TableCell>{{ formatDate(lote.data_aquisicao) }}</TableCell>
                  <TableCell>
                    <Badge :variant="getStatusVariant(lote.status)">
                      {{ getStatusLabel(lote.status) }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center gap-1">
                      <Button
                        variant="ghost"
                        size="icon-sm"
                        @click="openEditLoteDialog(lote)"
                      >
                        <Pencil class="size-4" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon-sm"
                        :disabled="lote.quantidade_disponivel < lote.quantidade_total"
                        @click="openDeleteLoteDialog(lote.id)"
                      >
                        <Trash2 class="size-4 text-destructive" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Spinner de carregamento -->
          <div v-if="isLoadingLotes" class="flex justify-center py-4">
            <Spinner size="sm" />
          </div>
        </CardContent>
      </Card>
    </template>

    <!-- Dialog Criar/Editar Lote -->
    <Dialog
      v-model:open="showLoteDialog"
      :title="editingLote ? 'Editar Lote' : 'Novo Lote'"
      :description="editingLote ? 'Altere os dados do lote' : 'Cadastre um novo lote de estoque'"
    >
      <form @submit.prevent="submitLote" class="space-y-4">
        <FormGroup>
          <FormField label="Codigo" required>
            <Input
              v-model="loteForm.codigo"
              placeholder="Ex: L001"
            />
          </FormField>

          <FormField label="Quantidade Total" required>
            <Input
              type="number"
              v-model="loteForm.quantidade_total"
              min="1"
              placeholder="0"
              :disabled="!!editingLote"
            />
          </FormField>
        </FormGroup>

        <FormGroup>
          <FormField label="Valor Total (R$)">
            <Input
              type="number"
              step="0.01"
              min="0"
              v-model="loteForm.valor_total"
              placeholder="0,00"
            />
          </FormField>

          <FormField label="Valor Frete (R$)">
            <Input
              type="number"
              step="0.01"
              min="0"
              v-model="loteForm.valor_frete"
              placeholder="0,00"
            />
          </FormField>
        </FormGroup>

        <FormGroup>
          <FormField label="Fornecedor">
            <Input
              v-model="loteForm.fornecedor"
              placeholder="Nome do fornecedor"
            />
          </FormField>

          <FormField label="Data de Aquisicao">
            <Input
              type="date"
              v-model="loteForm.data_aquisicao"
            />
          </FormField>
        </FormGroup>

        <FormGroup>
          <FormField label="Forma de Pagamento">
            <Input
              v-model="loteForm.forma_pagamento"
              placeholder="Ex: Boleto, Cartao"
            />
          </FormField>

          <FormField label="Nota Fiscal">
            <Input
              v-model="loteForm.nf"
              placeholder="Numero da NF"
            />
          </FormField>
        </FormGroup>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="showLoteDialog = false"
          >
            Cancelar
          </Button>
          <Button type="submit" :disabled="isSubmittingLote">
            <Spinner v-if="isSubmittingLote" size="sm" class="mr-2" />
            {{ editingLote ? "Salvar" : "Criar Lote" }}
          </Button>
        </DialogFooter>
      </form>
    </Dialog>

    <!-- Dialog Confirmar Exclusao -->
    <Dialog
      v-model:open="showDeleteLoteDialog"
      title="Remover Lote"
      description="Tem certeza que deseja remover este lote? Esta acao nao pode ser desfeita."
    >
      <DialogFooter>
        <Button
          variant="outline"
          @click="showDeleteLoteDialog = false"
        >
          Cancelar
        </Button>
        <Button
          variant="destructive"
          :disabled="isDeletingLote"
          @click="deleteLote"
        >
          <Spinner v-if="isDeletingLote" size="sm" class="mr-2" />
          Remover
        </Button>
      </DialogFooter>
    </Dialog>
  </div>
</template>
