<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { Combobox } from "@/components/ui/combobox"
import { Dialog, DialogFooter } from "@/components/ui/dialog"
import { FormField, FormGroup } from "@/components/forms"
import { Select } from "@/components/ui/select"
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from "@/components/ui/table"
import { Spinner } from "@/components/ui/spinner"
import { useNotification } from "@/composables"
import {
  Plus,
  Pencil,
  Trash2,
  Play,
  XCircle,
  Calendar,
  DollarSign,
  ArrowUpCircle,
  ArrowDownCircle,
  Clock,
} from "lucide-vue-next"
import api from "@/lib/api"
import type { ContratoAditivo, TipoAditivo, StatusAditivo, TipoAtivo, ApiResponse, PaginatedResponse } from "@/types"

const props = defineProps<{
  contratoId: string
  contratoStatus: string
}>()

const emit = defineEmits<{
  updated: []
}>()

const { success, error } = useNotification()

// Estado
const isLoading = ref(true)
const aditivos = ref<ContratoAditivo[]>([])
const tiposAtivos = ref<{ value: string; label: string }[]>([])

// Dialog de criacao/edicao
const showAditivoDialog = ref(false)
const editingAditivo = ref<ContratoAditivo | null>(null)
const aditivoForm = ref({
  tipo: "prorrogacao" as TipoAditivo,
  descricao: "",
  data_vigencia: new Date().toISOString().split("T")[0],
  nova_data_termino: "",
  valor_ajuste: 0,
  conceder_reembolso: false,
})
const isSubmittingAditivo = ref(false)

// Dialog de item
const showItemDialog = ref(false)
const currentAditivoId = ref<string | null>(null)
const itemForm = ref({
  tipo_ativo_id: "",
  quantidade_alterada: 1,
  valor_unitario: 0,
})
const isSubmittingItem = ref(false)

// Dialog de confirmacao
const showConfirmDialog = ref(false)
const confirmAction = ref<"ativar" | "cancelar" | "remover-item" | null>(null)
const confirmAditivoId = ref<string | null>(null)
const confirmItemId = ref<string | null>(null)
const isConfirming = ref(false)

// Opcoes de tipo de aditivo
const tiposAditivo: { value: TipoAditivo; label: string }[] = [
  { value: "prorrogacao", label: "Prorrogacao" },
  { value: "acrescimo", label: "Acrescimo" },
  { value: "reducao", label: "Reducao" },
  { value: "alteracao_valor", label: "Alteracao de Valor" },
]

// Computed
const isContratoAtivo = computed(() => props.contratoStatus === "ativo")

const confirmMessages: Record<string, { title: string; description: string }> = {
  ativar: {
    title: "Ativar Aditivo",
    description: "Ao ativar, as alteracoes serao aplicadas ao contrato. Deseja continuar?",
  },
  cancelar: {
    title: "Cancelar Aditivo",
    description: "Ao cancelar, as alteracoes serao revertidas (se aplicavel). Deseja continuar?",
  },
  "remover-item": {
    title: "Remover Item",
    description: "Deseja remover este item do aditivo?",
  },
}

// Carregar dados
onMounted(async () => {
  await Promise.all([loadAditivos(), loadTiposAtivos()])
})

async function loadAditivos(): Promise<void> {
  try {
    const response = await api.get<ApiResponse<ContratoAditivo[]>>(
      `/contratos/${props.contratoId}/aditivos`
    )
    aditivos.value = response.data
  } catch {
    // Silently fail
  } finally {
    isLoading.value = false
  }
}

async function loadTiposAtivos(): Promise<void> {
  try {
    const response = await api.get<PaginatedResponse<TipoAtivo>>("/tipos-ativos", {
      per_page: 100,
    })
    tiposAtivos.value = response.data.map((t) => ({
      value: t.id,
      label: t.nome,
    }))
  } catch {
    // Silently fail
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

function getStatusVariant(status: StatusAditivo): "default" | "success" | "secondary" | "destructive" {
  switch (status) {
    case "rascunho":
      return "secondary"
    case "ativo":
      return "success"
    case "cancelado":
      return "destructive"
    default:
      return "secondary"
  }
}

function getStatusLabel(status: StatusAditivo): string {
  switch (status) {
    case "rascunho":
      return "Rascunho"
    case "ativo":
      return "Ativo"
    case "cancelado":
      return "Cancelado"
    default:
      return status
  }
}

function getTipoLabel(tipo: TipoAditivo): string {
  const found = tiposAditivo.find((t) => t.value === tipo)
  return found?.label || tipo
}

function getTipoIcon(tipo: TipoAditivo) {
  switch (tipo) {
    case "prorrogacao":
      return Calendar
    case "acrescimo":
      return ArrowUpCircle
    case "reducao":
      return ArrowDownCircle
    case "alteracao_valor":
      return DollarSign
    default:
      return Clock
  }
}

// Handlers de aditivo
function openAddAditivoDialog(): void {
  editingAditivo.value = null
  aditivoForm.value = {
    tipo: "prorrogacao",
    descricao: "",
    data_vigencia: new Date().toISOString().split("T")[0],
    nova_data_termino: "",
    valor_ajuste: 0,
    conceder_reembolso: false,
  }
  showAditivoDialog.value = true
}

function openEditAditivoDialog(aditivo: ContratoAditivo): void {
  editingAditivo.value = aditivo
  aditivoForm.value = {
    tipo: aditivo.tipo,
    descricao: aditivo.descricao || "",
    data_vigencia: aditivo.data_vigencia,
    nova_data_termino: aditivo.nova_data_termino || "",
    valor_ajuste: aditivo.valor_ajuste || 0,
    conceder_reembolso: aditivo.conceder_reembolso,
  }
  showAditivoDialog.value = true
}

async function submitAditivo(): Promise<void> {
  isSubmittingAditivo.value = true
  try {
    if (editingAditivo.value) {
      await api.put(
        `/contratos/${props.contratoId}/aditivos/${editingAditivo.value.id}`,
        aditivoForm.value
      )
      success("Sucesso!", "Aditivo atualizado")
    } else {
      await api.post(`/contratos/${props.contratoId}/aditivos`, aditivoForm.value)
      success("Sucesso!", "Aditivo criado")
    }
    showAditivoDialog.value = false
    await loadAditivos()
    emit("updated")
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    error("Erro", apiError.message || "Erro ao salvar aditivo")
  } finally {
    isSubmittingAditivo.value = false
  }
}

// Handlers de item
function openAddItemDialog(aditivoId: string): void {
  currentAditivoId.value = aditivoId
  itemForm.value = {
    tipo_ativo_id: "",
    quantidade_alterada: 1,
    valor_unitario: 0,
  }
  showItemDialog.value = true
}

async function submitItem(): Promise<void> {
  if (!currentAditivoId.value) return

  isSubmittingItem.value = true
  try {
    await api.post(
      `/contratos/${props.contratoId}/aditivos/${currentAditivoId.value}/itens`,
      itemForm.value
    )
    success("Sucesso!", "Item adicionado")
    showItemDialog.value = false
    await loadAditivos()
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    error("Erro", apiError.message || "Erro ao adicionar item")
  } finally {
    isSubmittingItem.value = false
  }
}

// Handlers de confirmacao
function openConfirmDialog(
  action: typeof confirmAction.value,
  aditivoId: string,
  itemId?: string
): void {
  confirmAction.value = action
  confirmAditivoId.value = aditivoId
  confirmItemId.value = itemId || null
  showConfirmDialog.value = true
}

async function executeConfirmAction(): Promise<void> {
  if (!confirmAditivoId.value || !confirmAction.value) return

  isConfirming.value = true
  try {
    switch (confirmAction.value) {
      case "ativar":
        await api.post(
          `/contratos/${props.contratoId}/aditivos/${confirmAditivoId.value}/ativar`
        )
        success("Sucesso!", "Aditivo ativado")
        break
      case "cancelar":
        await api.post(
          `/contratos/${props.contratoId}/aditivos/${confirmAditivoId.value}/cancelar`
        )
        success("Sucesso!", "Aditivo cancelado")
        break
      case "remover-item":
        if (confirmItemId.value) {
          await api.delete(
            `/contratos/${props.contratoId}/aditivos/${confirmAditivoId.value}/itens/${confirmItemId.value}`
          )
          success("Sucesso!", "Item removido")
        }
        break
    }
    showConfirmDialog.value = false
    await loadAditivos()
    emit("updated")
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    error("Erro", apiError.message || "Erro ao executar acao")
  } finally {
    isConfirming.value = false
  }
}

// Computed para mostrar campos especificos
const showNovaDataTermino = computed(
  () => aditivoForm.value.tipo === "prorrogacao"
)
const showValorAjuste = computed(
  () => aditivoForm.value.tipo === "alteracao_valor"
)
const _showItens = computed(
  () =>
    aditivoForm.value.tipo === "acrescimo" ||
    aditivoForm.value.tipo === "reducao"
)
const showReembolso = computed(() => aditivoForm.value.tipo === "reducao")
</script>

<template>
  <Card v-if="isContratoAtivo">
    <CardHeader class="flex flex-row items-center justify-between">
      <CardTitle>Aditivos</CardTitle>
      <Button size="sm" @click="openAddAditivoDialog">
        <Plus class="size-4 mr-2" />
        Novo Aditivo
      </Button>
    </CardHeader>
    <CardContent>
      <div v-if="isLoading" class="flex items-center justify-center py-8">
        <Spinner size="lg" />
      </div>

      <div v-else-if="aditivos.length === 0" class="text-center py-8 text-muted-foreground">
        Nenhum aditivo criado para este contrato.
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="aditivo in aditivos"
          :key="aditivo.id"
          class="border rounded-lg p-4 space-y-4"
        >
          <!-- Header do aditivo -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="rounded-lg bg-primary/10 p-2">
                <component :is="getTipoIcon(aditivo.tipo)" class="size-5 text-primary" />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-medium">{{ getTipoLabel(aditivo.tipo) }}</span>
                  <Badge :variant="getStatusVariant(aditivo.status)">
                    {{ getStatusLabel(aditivo.status) }}
                  </Badge>
                </div>
                <p class="text-sm text-muted-foreground">
                  Vigencia: {{ formatDate(aditivo.data_vigencia) }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <Button
                v-if="aditivo.status === 'rascunho'"
                variant="ghost"
                size="icon-sm"
                @click="openEditAditivoDialog(aditivo)"
              >
                <Pencil class="size-4" />
              </Button>
              <Button
                v-if="aditivo.status === 'rascunho'"
                variant="outline"
                size="sm"
                @click="openConfirmDialog('ativar', aditivo.id)"
              >
                <Play class="size-4 mr-1" />
                Ativar
              </Button>
              <Button
                v-if="aditivo.status === 'rascunho' || aditivo.status === 'ativo'"
                variant="ghost"
                size="icon-sm"
                @click="openConfirmDialog('cancelar', aditivo.id)"
              >
                <XCircle class="size-4 text-destructive" />
              </Button>
            </div>
          </div>

          <!-- Detalhes do aditivo -->
          <div v-if="aditivo.descricao" class="text-sm text-muted-foreground">
            {{ aditivo.descricao }}
          </div>

          <div v-if="aditivo.tipo === 'prorrogacao' && aditivo.nova_data_termino" class="text-sm">
            <span class="text-muted-foreground">Nova data de termino:</span>
            <span class="ml-2 font-medium">{{ formatDate(aditivo.nova_data_termino) }}</span>
          </div>

          <div v-if="aditivo.tipo === 'alteracao_valor' && aditivo.valor_ajuste" class="text-sm">
            <span class="text-muted-foreground">Ajuste de valor:</span>
            <span class="ml-2 font-medium" :class="{ 'text-green-600': (aditivo.valor_ajuste ?? 0) > 0, 'text-red-600': (aditivo.valor_ajuste ?? 0) < 0 }">
              {{ formatCurrency(aditivo.valor_ajuste) }}
            </span>
          </div>

          <!-- Itens do aditivo -->
          <div v-if="(aditivo.tipo === 'acrescimo' || aditivo.tipo === 'reducao') && aditivo.itens && aditivo.itens.length > 0">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium">Itens</span>
              <Button
                v-if="aditivo.status === 'rascunho'"
                variant="ghost"
                size="sm"
                @click="openAddItemDialog(aditivo.id)"
              >
                <Plus class="size-4 mr-1" />
                Adicionar
              </Button>
            </div>
            <div class="rounded-md border">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Ativo</TableHead>
                    <TableHead>Quantidade</TableHead>
                    <TableHead>Valor Unit.</TableHead>
                    <TableHead v-if="aditivo.status === 'rascunho'" class="w-[80px]">Acoes</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="item in aditivo.itens" :key="item.id">
                    <TableCell>{{ item.tipo_ativo?.nome || "-" }}</TableCell>
                    <TableCell>
                      <span :class="{ 'text-green-600': item.quantidade_alterada > 0, 'text-red-600': item.quantidade_alterada < 0 }">
                        {{ item.quantidade_alterada > 0 ? '+' : '' }}{{ item.quantidade_alterada }}
                      </span>
                    </TableCell>
                    <TableCell>{{ formatCurrency(item.valor_unitario) }}</TableCell>
                    <TableCell v-if="aditivo.status === 'rascunho'">
                      <Button
                        variant="ghost"
                        size="icon-sm"
                        @click="openConfirmDialog('remover-item', aditivo.id, item.id)"
                      >
                        <Trash2 class="size-4 text-destructive" />
                      </Button>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </div>

          <!-- Botao para adicionar itens se vazio -->
          <div
            v-if="(aditivo.tipo === 'acrescimo' || aditivo.tipo === 'reducao') && (!aditivo.itens || aditivo.itens.length === 0) && aditivo.status === 'rascunho'"
            class="text-center py-4 border rounded-lg border-dashed"
          >
            <Button variant="ghost" size="sm" @click="openAddItemDialog(aditivo.id)">
              <Plus class="size-4 mr-2" />
              Adicionar Item
            </Button>
          </div>
        </div>
      </div>
    </CardContent>

    <!-- Dialog Criar/Editar Aditivo -->
    <Dialog
      v-model:open="showAditivoDialog"
      :title="editingAditivo ? 'Editar Aditivo' : 'Novo Aditivo'"
      :description="editingAditivo ? 'Altere os dados do aditivo' : 'Crie um novo aditivo para o contrato'"
    >
      <form @submit.prevent="submitAditivo" class="space-y-4">
        <FormField label="Tipo de Aditivo" required>
          <Select
            v-model="aditivoForm.tipo"
            :options="tiposAditivo"
            :disabled="!!editingAditivo"
          />
        </FormField>

        <FormField label="Data de Vigencia" required>
          <Input type="date" v-model="aditivoForm.data_vigencia" />
        </FormField>

        <FormField v-if="showNovaDataTermino" label="Nova Data de Termino" required>
          <Input type="date" v-model="aditivoForm.nova_data_termino" />
        </FormField>

        <FormField v-if="showValorAjuste" label="Valor do Ajuste (R$)" required>
          <Input
            type="number"
            v-model.number="aditivoForm.valor_ajuste"
            step="0.01"
            placeholder="Positivo para acrescimo, negativo para reducao"
          />
        </FormField>

        <FormField v-if="showReembolso" label="Conceder Reembolso">
          <div class="flex items-center gap-2">
            <input
              type="checkbox"
              v-model="aditivoForm.conceder_reembolso"
              class="rounded border-gray-300"
            />
            <span class="text-sm text-muted-foreground">
              Conceder credito proporcional ao locatario (Stripe)
            </span>
          </div>
        </FormField>

        <FormField label="Descricao">
          <Textarea
            v-model="aditivoForm.descricao"
            placeholder="Descreva o motivo do aditivo..."
            rows="3"
          />
        </FormField>

        <DialogFooter>
          <Button type="button" variant="outline" @click="showAditivoDialog = false">
            Cancelar
          </Button>
          <Button type="submit" :disabled="isSubmittingAditivo">
            <Spinner v-if="isSubmittingAditivo" size="sm" class="mr-2" />
            {{ editingAditivo ? "Salvar" : "Criar" }}
          </Button>
        </DialogFooter>
      </form>
    </Dialog>

    <!-- Dialog Adicionar Item -->
    <Dialog
      v-model:open="showItemDialog"
      title="Adicionar Item ao Aditivo"
      description="Adicione um item de acrescimo ou reducao"
    >
      <form @submit.prevent="submitItem" class="space-y-4">
        <FormField label="Ativo" required>
          <Combobox
            v-model="itemForm.tipo_ativo_id"
            :options="tiposAtivos"
            placeholder="Buscar ativo..."
            search-placeholder="Digite para buscar..."
            empty-text="Nenhum ativo encontrado"
          />
        </FormField>

        <FormGroup>
          <FormField label="Quantidade" required>
            <Input
              type="number"
              v-model.number="itemForm.quantidade_alterada"
              placeholder="Positivo=acrescimo, Negativo=reducao"
            />
            <p class="text-xs text-muted-foreground mt-1">
              Use valores negativos para reducao
            </p>
          </FormField>

          <FormField label="Valor Unitario (R$)">
            <Input
              type="number"
              v-model.number="itemForm.valor_unitario"
              min="0"
              step="0.01"
            />
          </FormField>
        </FormGroup>

        <DialogFooter>
          <Button type="button" variant="outline" @click="showItemDialog = false">
            Cancelar
          </Button>
          <Button type="submit" :disabled="isSubmittingItem">
            <Spinner v-if="isSubmittingItem" size="sm" class="mr-2" />
            Adicionar
          </Button>
        </DialogFooter>
      </form>
    </Dialog>

    <!-- Dialog Confirmacao -->
    <Dialog
      v-model:open="showConfirmDialog"
      :title="confirmAction ? confirmMessages[confirmAction]?.title : ''"
      :description="confirmAction ? confirmMessages[confirmAction]?.description : ''"
    >
      <DialogFooter>
        <Button variant="outline" @click="showConfirmDialog = false">
          Cancelar
        </Button>
        <Button
          :variant="confirmAction === 'ativar' ? 'default' : 'destructive'"
          :disabled="isConfirming"
          @click="executeConfirmAction"
        >
          <Spinner v-if="isConfirming" size="sm" class="mr-2" />
          Confirmar
        </Button>
      </DialogFooter>
    </Dialog>
  </Card>
</template>
