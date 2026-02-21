<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import { useRouter, useRoute } from "vue-router"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Combobox } from "@/components/ui/combobox"
import { Dialog, DialogFooter } from "@/components/ui/dialog"
import { FormField, FormGroup } from "@/components/forms"
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
import { ContratoPagamentoStripe, ContratoPagamentos, ContratoCheckout, ContratoAditivos } from "@/components/app"
import { Select } from "@/components/ui/select"
import {
  ArrowLeft,
  Calendar,
  User,
  FileText,
  Package,
  Plus,
  Pencil,
  Trash2,
  Play,
  XCircle,
  CheckCircle,
  CreditCard,
  AlertTriangle,
  Download,
  Upload,
  FileCheck,
} from "lucide-vue-next"
import api from "@/lib/api"
import type { Contrato, ContratoItem, ContratoStatus, TipoCobranca, TipoAtivo, TipoAtivoForm, ContratoForm, Pessoa, ApiResponse, PaginatedResponse } from "@/types"

const router = useRouter()
const route = useRoute()
const { success, error } = useNotification()

// Estado principal
const isLoading = ref(true)
const contrato = ref<Contrato | null>(null)
const tiposAtivos = ref<{ value: string; label: string; valorMensal: number; valorDiaria: number }[]>([])
const locatarios = ref<{ value: string; label: string }[]>([])

// Estado do dialog de edição do contrato
const showEditDialog = ref(false)
const editForm = ref<ContratoForm>({
  locatario_id: "",
  data_inicio: "",
  data_termino: "",
  observacoes: "",
  tipo_cobranca: "sem_cobranca",
})
const isSubmittingEdit = ref(false)

// Opcoes de tipo de cobranca
const tiposCobranca: { value: TipoCobranca; label: string }[] = [
  { value: "sem_cobranca", label: "Sem cobranca via sistema" },
  { value: "antecipado_stripe", label: "Antecipado - Cartao" },
  { value: "antecipado_pix", label: "Antecipado - PIX" },
  { value: "recorrente_stripe", label: "Recorrente - Stripe" },
  { value: "recorrente_manual", label: "Recorrente - Manual" },
]

// Estado do dialog de item
const showItemDialog = ref(false)
const editingItem = ref<ContratoItem | null>(null)
const itemForm = ref({
  tipo_ativo_id: "",
  quantidade: 1,
  valor_unitario: 0,
  periodo_aluguel: "diaria" as "diaria" | "mensal",
})
const isSubmittingItem = ref(false)

// Estado do dialog de confirmacao
const showConfirmDialog = ref(false)
const confirmAction = ref<"ativar" | "cancelar" | "finalizar" | "remover-item" | null>(null)
const confirmItemId = ref<string | null>(null)
const isConfirming = ref(false)

// Estado do dialog de erro de estoque
const showEstoqueErrorDialog = ref(false)
const estoqueError = ref<{
  message: string
  tipoAtivo: string
  tipoAtivoId: string
  quantidadeSolicitada: number
  quantidadeDisponivel: number
  quantidadeFaltante: number
} | null>(null)

// Estado do dialog de tipo de cobranca
const showTipoCobrancaDialog = ref(false)
const tipoCobrancaForm = ref<TipoCobranca>("sem_cobranca")
const isSubmittingTipoCobranca = ref(false)

// Estado do documento
const isGeneratingDocument = ref(false)
const isUploadingDocument = ref(false)
const documentFile = ref<File | null>(null)

// Estado do dialog de ativo
const showTipoAtivoDialog = ref(false)
const tipoAtivoForm = useForm<TipoAtivoForm>({
  initialValues: {
    nome: "",
    descricao: "",
    unidade_medida: "un",
    valor_mensal_sugerido: 0,
  },
  validate(values) {
    const errors: Partial<Record<keyof TipoAtivoForm, string>> = {}
    if (!values.nome) {
      errors.nome = "Nome e obrigatorio"
    }
    if (!values.unidade_medida) {
      errors.unidade_medida = "Unidade de medida e obrigatoria"
    }
    return errors
  },
  async onSubmit(values) {
    const response = await api.post<{ data: TipoAtivo }>("/tipos-ativos", values)
    success("Sucesso!", "Ativo criado")

    // Adiciona o novo tipo a lista e seleciona
    const newTipo = response.data
    tiposAtivos.value.push({
      value: newTipo.id,
      label: newTipo.nome,
      valorMensal: newTipo.valor_mensal_sugerido,
      valorDiaria: newTipo.valor_diaria_sugerido,
    })
    itemForm.value.tipo_ativo_id = newTipo.id
    // Preenche valor baseado no periodo selecionado
    itemForm.value.valor_unitario = itemForm.value.periodo_aluguel === 'mensal'
      ? newTipo.valor_mensal_sugerido
      : newTipo.valor_diaria_sugerido

    showTipoAtivoDialog.value = false
    tipoAtivoForm.reset()
  },
})

function openTipoAtivoDialog() {
  tipoAtivoForm.reset()
  showTipoAtivoDialog.value = true
}

// Computed
const isRascunho = computed(() => contrato.value?.status === "rascunho")
const _isAguardandoPagamento = computed(() => contrato.value?.status === "aguardando_pagamento")
const isAtivo = computed(() => contrato.value?.status === "ativo")

const diasLocacao = computed(() => {
  if (!contrato.value) return 0
  const inicio = new Date(contrato.value.data_inicio)
  const termino = new Date(contrato.value.data_termino)
  const diff = termino.getTime() - inicio.getTime()
  return Math.ceil(diff / (1000 * 60 * 60 * 24)) + 1
})

const confirmMessages: Record<string, { title: string; description: string }> = {
  ativar: {
    title: "Ativar Contrato",
    description: "Ao ativar, os itens serao alocados dos lotes disponiveis. Esta acao nao pode ser desfeita. Deseja continuar?",
  },
  cancelar: {
    title: "Cancelar Contrato",
    description: "Ao cancelar, os itens alocados serao liberados. Esta acao nao pode ser desfeita. Deseja continuar?",
  },
  finalizar: {
    title: "Finalizar Contrato",
    description: "Ao finalizar, os itens alocados serao liberados e o contrato sera encerrado. Deseja continuar?",
  },
  "remover-item": {
    title: "Remover Item",
    description: "Deseja remover este item do contrato?",
  },
}

// Carregar dados
onMounted(async () => {
  await Promise.all([loadContrato(), loadTiposAtivos(), loadLocatarios()])
})

async function loadContrato(): Promise<void> {
  try {
    const response = await api.get<ApiResponse<Contrato>>(
      `/contratos/${route.params.id}`,
    )
    contrato.value = response.data
  } catch {
    error("Erro", "Contrato nao encontrado")
    router.push({ name: "contratos.index" })
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
      valorMensal: t.valor_mensal_sugerido,
      valorDiaria: t.valor_diaria_sugerido,
    }))
  } catch {
    // Silently fail - user can still view contract
  }
}

async function loadLocatarios(): Promise<void> {
  try {
    const response = await api.get<PaginatedResponse<Pessoa>>("/locatarios", {
      per_page: 100,
    })
    locatarios.value = response.data.map((p) => ({
      value: p.id,
      label: p.nome,
    }))
  } catch {
    // Silently fail - user can still view contract
  }
}

// Handlers de edição do contrato
function openEditDialog(): void {
  if (!contrato.value) return
  editForm.value = {
    locatario_id: contrato.value.locatario_id,
    data_inicio: contrato.value.data_inicio,
    data_termino: contrato.value.data_termino,
    observacoes: contrato.value.observacoes || "",
    tipo_cobranca: contrato.value.tipo_cobranca || "sem_cobranca",
  }
  showEditDialog.value = true
}

async function submitEditContrato(): Promise<void> {
  if (!contrato.value) return

  isSubmittingEdit.value = true
  try {
    await api.put(`/contratos/${contrato.value.id}`, editForm.value)
    success("Sucesso!", "Contrato atualizado")
    showEditDialog.value = false
    await loadContrato()
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    error("Erro", apiError.message || "Erro ao atualizar contrato")
  } finally {
    isSubmittingEdit.value = false
  }
}

// Handlers de tipo de cobranca
function openTipoCobrancaDialog(): void {
  if (!contrato.value) return
  tipoCobrancaForm.value = contrato.value.tipo_cobranca || "sem_cobranca"
  showTipoCobrancaDialog.value = true
}

async function submitTipoCobranca(): Promise<void> {
  if (!contrato.value) return

  isSubmittingTipoCobranca.value = true
  try {
    await api.put(`/contratos/${contrato.value.id}/tipo-cobranca`, {
      tipo_cobranca: tipoCobrancaForm.value,
    })
    success("Sucesso!", "Tipo de cobranca atualizado")
    showTipoCobrancaDialog.value = false
    await loadContrato()
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    error("Erro", apiError.message || "Erro ao atualizar tipo de cobranca")
  } finally {
    isSubmittingTipoCobranca.value = false
  }
}

// Formatadores
function formatCurrency(value: number): string {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value)
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString("pt-BR")
}

function getStatusVariant(status: ContratoStatus): "default" | "success" | "secondary" | "destructive" | "warning" {
  switch (status) {
    case "rascunho":
      return "secondary"
    case "aguardando_pagamento":
      return "warning"
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
    case "aguardando_pagamento":
      return "Aguardando Pagamento"
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

function getTipoCobrancaLabel(tipo: TipoCobranca | undefined): string {
  if (!tipo) return "Nao definido"
  const found = tiposCobranca.find((t) => t.value === tipo)
  return found?.label || tipo
}

// Handlers de item
function openAddItemDialog(): void {
  editingItem.value = null
  itemForm.value = {
    tipo_ativo_id: "",
    quantidade: 1,
    valor_unitario: 0,
    periodo_aluguel: "diaria",
  }
  showItemDialog.value = true
}

function openEditItemDialog(item: ContratoItem): void {
  editingItem.value = item
  itemForm.value = {
    tipo_ativo_id: item.tipo_ativo_id,
    quantidade: item.quantidade,
    valor_unitario: item.valor_unitario,
    periodo_aluguel: item.periodo_aluguel,
  }
  showItemDialog.value = true
}

function onTipoAtivoChange(tipoAtivoId: string): void {
  const tipo = tiposAtivos.value.find((t) => t.value === tipoAtivoId)
  if (tipo && !editingItem.value) {
    // Preenche valor baseado no periodo selecionado
    itemForm.value.valor_unitario = itemForm.value.periodo_aluguel === 'mensal'
      ? tipo.valorMensal
      : tipo.valorDiaria
  }
}

function onPeriodoChange(): void {
  const tipo = tiposAtivos.value.find((t) => t.value === itemForm.value.tipo_ativo_id)
  if (tipo && !editingItem.value) {
    // Atualiza valor baseado no novo periodo
    itemForm.value.valor_unitario = itemForm.value.periodo_aluguel === 'mensal'
      ? tipo.valorMensal
      : tipo.valorDiaria
  }
}

// Computed para subtotal do item no formulario
const subtotalItem = computed(() => {
  if (!contrato.value || !itemForm.value.valor_unitario || !itemForm.value.quantidade) {
    return 0
  }

  const quantidade = Number(itemForm.value.quantidade)
  const valorUnitario = Number(itemForm.value.valor_unitario)

  if (itemForm.value.periodo_aluguel === 'mensal') {
    // Calcula meses (arredonda para cima)
    const meses = Math.ceil(diasLocacao.value / 30)
    return quantidade * valorUnitario * meses
  }

  return quantidade * valorUnitario * diasLocacao.value
})

async function submitItem(): Promise<void> {
  if (!contrato.value) return

  isSubmittingItem.value = true
  try {
    if (editingItem.value) {
      // Atualizar item
      await api.put(
        `/contratos/${contrato.value.id}/itens/${editingItem.value.id}`,
        itemForm.value,
      )
      success("Sucesso!", "Item atualizado")
    } else {
      // Adicionar item
      await api.post(`/contratos/${contrato.value.id}/itens`, itemForm.value)
      success("Sucesso!", "Item adicionado")
    }
    showItemDialog.value = false
    await loadContrato()
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    error("Erro", apiError.message || "Erro ao salvar item")
  } finally {
    isSubmittingItem.value = false
  }
}

// Handlers de confirmacao
function openConfirmDialog(action: typeof confirmAction.value, itemId?: string): void {
  confirmAction.value = action
  confirmItemId.value = itemId || null
  showConfirmDialog.value = true
}

async function executeConfirmAction(): Promise<void> {
  if (!contrato.value || !confirmAction.value) return

  isConfirming.value = true
  try {
    switch (confirmAction.value) {
      case "ativar":
        await api.post(`/contratos/${contrato.value.id}/ativar`)
        success("Sucesso!", "Contrato ativado")
        break
      case "cancelar":
        await api.post(`/contratos/${contrato.value.id}/cancelar`)
        success("Sucesso!", "Contrato cancelado")
        break
      case "finalizar":
        await api.post(`/contratos/${contrato.value.id}/finalizar`)
        success("Sucesso!", "Contrato finalizado")
        break
      case "remover-item":
        if (confirmItemId.value) {
          await api.delete(`/contratos/${contrato.value.id}/itens/${confirmItemId.value}`)
          success("Sucesso!", "Item removido")
        }
        break
    }
    showConfirmDialog.value = false
    await loadContrato()
  } catch (err: unknown) {
    const apiError = err as {
      message?: string
      error_type?: string
      tipo_ativo?: string
      tipo_ativo_id?: string
      quantidade_solicitada?: number
      quantidade_disponivel?: number
      quantidade_faltante?: number
    }

    // Trata erro de quantidade indisponivel com dialog especial
    if (apiError.error_type === 'quantidade_indisponivel') {
      showConfirmDialog.value = false
      estoqueError.value = {
        message: apiError.message || 'Estoque insuficiente',
        tipoAtivo: apiError.tipo_ativo || '',
        tipoAtivoId: apiError.tipo_ativo_id || '',
        quantidadeSolicitada: apiError.quantidade_solicitada || 0,
        quantidadeDisponivel: apiError.quantidade_disponivel || 0,
        quantidadeFaltante: apiError.quantidade_faltante || 0,
      }
      showEstoqueErrorDialog.value = true
    } else {
      error("Erro", apiError.message || "Erro ao executar acao")
    }
  } finally {
    isConfirming.value = false
  }
}

// Handlers de documento
async function gerarDocumento(): Promise<void> {
  if (!contrato.value) return

  isGeneratingDocument.value = true
  try {
    const blob = await api.getBlob(`/contratos/${contrato.value.id}/documento`)

    // Cria link de download
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `contrato-${contrato.value.codigo}.docx`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    success("Sucesso!", "Documento gerado com sucesso")
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    error("Erro", apiError.message || "Erro ao gerar documento")
  } finally {
    isGeneratingDocument.value = false
  }
}

function onFileChange(event: Event): void {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    documentFile.value = target.files[0]
  }
}

async function uploadDocumentoAssinado(): Promise<void> {
  if (!contrato.value || !documentFile.value) return

  isUploadingDocument.value = true
  try {
    const formData = new FormData()
    formData.append('documento', documentFile.value)

    await api.postFormData(`/contratos/${contrato.value.id}/documento`, formData)

    success("Sucesso!", "Documento assinado enviado com sucesso")
    documentFile.value = null
    await loadContrato()
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    error("Erro", apiError.message || "Erro ao enviar documento")
  } finally {
    isUploadingDocument.value = false
  }
}

async function downloadDocumentoAssinado(): Promise<void> {
  if (!contrato.value) return

  try {
    const blob = await api.getBlob(`/contratos/${contrato.value.id}/documento/assinado`)

    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `contrato-${contrato.value.codigo}-assinado.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    error("Erro", apiError.message || "Erro ao baixar documento")
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Button variant="ghost" size="icon" @click="router.back()">
          <ArrowLeft class="size-5" />
        </Button>
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold">
              Contrato {{ contrato?.codigo || "..." }}
            </h1>
            <Badge
              v-if="contrato"
              :variant="getStatusVariant(contrato.status)"
            >
              {{ getStatusLabel(contrato.status) }}
            </Badge>
          </div>
          <p class="text-muted-foreground">Detalhes do contrato de locacao</p>
        </div>
      </div>

      <!-- Acoes de status -->
      <div v-if="contrato" class="flex items-center gap-2">
        <Button
          v-if="isRascunho"
          variant="outline"
          @click="openEditDialog"
        >
          <Pencil class="size-4 mr-2" />
          Editar
        </Button>
        <Button
          v-if="isRascunho"
          variant="default"
          @click="openConfirmDialog('ativar')"
        >
          <Play class="size-4 mr-2" />
          Ativar
        </Button>
        <Button
          v-if="isAtivo"
          variant="outline"
          @click="openConfirmDialog('finalizar')"
        >
          <CheckCircle class="size-4 mr-2" />
          Finalizar
        </Button>
        <Button
          v-if="isRascunho || isAtivo"
          variant="destructive"
          @click="openConfirmDialog('cancelar')"
        >
          <XCircle class="size-4 mr-2" />
          Cancelar
        </Button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <Spinner size="lg" />
    </div>

    <template v-else-if="contrato">
      <!-- Info cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="rounded-lg bg-primary/10 p-2">
                <User class="size-5 text-primary" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Locatario</p>
                <p class="font-medium">{{ contrato.locatario?.nome || "-" }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="rounded-lg bg-primary/10 p-2">
                <Calendar class="size-5 text-primary" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Periodo</p>
                <p class="font-medium">
                  {{ formatDate(contrato.data_inicio) }} - {{ formatDate(contrato.data_termino) }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="rounded-lg bg-primary/10 p-2">
                <FileText class="size-5 text-primary" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Dias de Locacao</p>
                <p class="font-medium">{{ diasLocacao }} dias</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="rounded-lg bg-primary/10 p-2">
                <Package class="size-5 text-primary" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Valor Total</p>
                <p class="font-medium text-lg">{{ formatCurrency(contrato.valor_total) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card :class="{ 'cursor-pointer hover:bg-muted/50': isRascunho }" @click="isRascunho && openTipoCobrancaDialog()">
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="rounded-lg bg-primary/10 p-2">
                <CreditCard class="size-5 text-primary" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-muted-foreground">Tipo de Cobranca</p>
                <p class="font-medium text-sm truncate">{{ getTipoCobrancaLabel(contrato.tipo_cobranca) }}</p>
              </div>
              <Pencil v-if="isRascunho" class="size-4 text-muted-foreground" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Itens do contrato -->
      <Card>
        <CardHeader class="flex flex-row items-center justify-between">
          <CardTitle>Itens do Contrato</CardTitle>
          <Button v-if="isRascunho" size="sm" @click="openAddItemDialog">
            <Plus class="size-4 mr-2" />
            Adicionar Item
          </Button>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Ativo</TableHead>
                  <TableHead>Qtd</TableHead>
                  <TableHead>Valor Unit.</TableHead>
                  <TableHead>Periodo</TableHead>
                  <TableHead>Subtotal</TableHead>
                  <TableHead v-if="isRascunho" class="w-[100px]">Acoes</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableEmpty
                  v-if="!contrato.itens || contrato.itens.length === 0"
                  :colspan="isRascunho ? 6 : 5"
                  message="Nenhum item adicionado ao contrato"
                />
                <TableRow v-else v-for="item in contrato.itens" :key="item.id">
                  <TableCell>{{ item.tipo_ativo?.nome || "-" }}</TableCell>
                  <TableCell>{{ item.quantidade }}</TableCell>
                  <TableCell>{{ formatCurrency(item.valor_unitario) }}</TableCell>
                  <TableCell>
                    <Badge :variant="item.periodo_aluguel === 'mensal' ? 'default' : 'secondary'">
                      {{ item.periodo_aluguel === 'mensal' ? 'Mensal' : 'Diaria' }}
                    </Badge>
                  </TableCell>
                  <TableCell class="font-medium">
                    {{ formatCurrency(item.valor_total_item) }}
                  </TableCell>
                  <TableCell v-if="isRascunho">
                    <div class="flex items-center gap-1">
                      <Button
                        variant="ghost"
                        size="icon-sm"
                        @click="openEditItemDialog(item)"
                      >
                        <Pencil class="size-4" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon-sm"
                        @click="openConfirmDialog('remover-item', item.id)"
                      >
                        <Trash2 class="size-4 text-destructive" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <!-- Checkout para pagamento antecipado -->
      <ContratoCheckout
        :contrato-id="contrato.id"
        :contrato-status="contrato.status"
        :tipo-cobranca="contrato.tipo_cobranca"
        :valor-total="contrato.valor_total"
        @updated="loadContrato"
      />

      <!-- Pagamento Stripe Recorrente -->
      <ContratoPagamentoStripe
        v-if="contrato.tipo_cobranca === 'recorrente_stripe'"
        :contrato-id="contrato.id"
        :contrato-status="contrato.status"
        @updated="loadContrato"
      />

      <!-- Gestao de Pagamentos -->
      <ContratoPagamentos
        :contrato-id="contrato.id"
        :contrato-status="contrato.status"
        :tipo-cobranca="contrato.tipo_cobranca"
        @updated="loadContrato"
      />

      <!-- Aditivos do Contrato -->
      <ContratoAditivos
        :contrato-id="contrato.id"
        :contrato-status="contrato.status"
        @updated="loadContrato"
      />

      <!-- Documento do Contrato -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <FileText class="size-5" />
            Documento do Contrato
          </CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <!-- Gerar documento -->
          <div class="flex items-center justify-between p-4 rounded-lg bg-muted">
            <div>
              <p class="font-medium">Gerar Documento DOCX</p>
              <p class="text-sm text-muted-foreground">
                Gere o contrato preenchido para edição e assinatura
              </p>
            </div>
            <Button
              :disabled="isGeneratingDocument || !contrato.locatario"
              @click="gerarDocumento"
            >
              <Spinner v-if="isGeneratingDocument" size="sm" class="mr-2" />
              <Download v-else class="size-4 mr-2" />
              Gerar Documento
            </Button>
          </div>

          <!-- Upload documento assinado -->
          <div class="p-4 rounded-lg border border-dashed space-y-3">
            <div class="flex items-center gap-2">
              <Upload class="size-5 text-muted-foreground" />
              <div>
                <p class="font-medium">Upload do Documento Assinado</p>
                <p class="text-sm text-muted-foreground">
                  Envie o contrato assinado (PDF, max 10MB)
                </p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <input
                type="file"
                accept=".pdf,application/pdf"
                class="flex-1 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary file:text-primary-foreground hover:file:bg-primary/90"
                @change="onFileChange"
              />
              <Button
                :disabled="!documentFile || isUploadingDocument"
                @click="uploadDocumentoAssinado"
              >
                <Spinner v-if="isUploadingDocument" size="sm" class="mr-2" />
                Enviar
              </Button>
            </div>
          </div>

          <!-- Documento assinado existente -->
          <div
            v-if="contrato.documento_assinado_path"
            class="flex items-center justify-between p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800"
          >
            <div class="flex items-center gap-3">
              <FileCheck class="size-5 text-green-600 dark:text-green-400" />
              <div>
                <p class="font-medium text-green-800 dark:text-green-200">
                  Documento Assinado
                </p>
                <p class="text-sm text-green-600 dark:text-green-400">
                  O contrato assinado esta disponivel para download
                </p>
              </div>
            </div>
            <Button variant="outline" @click="downloadDocumentoAssinado">
              <Download class="size-4 mr-2" />
              Baixar PDF
            </Button>
          </div>
        </CardContent>
      </Card>

    </template>

    <!-- Dialog Adicionar/Editar Item -->
    <Dialog
      v-model:open="showItemDialog"
      :title="editingItem ? 'Editar Item' : 'Adicionar Item'"
      :description="editingItem ? 'Altere os dados do item' : 'Adicione um novo item ao contrato'"
    >
      <form @submit.prevent="submitItem" class="space-y-4">
        <FormField label="Ativo" required>
          <Combobox
            v-model="itemForm.tipo_ativo_id"
            :options="tiposAtivos"
            placeholder="Buscar ativo..."
            search-placeholder="Digite para buscar..."
            empty-text="Nenhum ativo encontrado"
            :disabled="!!editingItem"
            allow-create
            create-text="Criar novo ativo"
            @update:model-value="onTipoAtivoChange"
            @create="openTipoAtivoDialog"
          />
        </FormField>

        <FormField label="Periodo de Aluguel" required>
          <div class="flex gap-2">
            <Button
              type="button"
              size="sm"
              :variant="itemForm.periodo_aluguel === 'diaria' ? 'default' : 'outline'"
              @click="itemForm.periodo_aluguel = 'diaria'; onPeriodoChange()"
            >
              Diaria
            </Button>
            <Button
              type="button"
              size="sm"
              :variant="itemForm.periodo_aluguel === 'mensal' ? 'default' : 'outline'"
              @click="itemForm.periodo_aluguel = 'mensal'; onPeriodoChange()"
            >
              Mensal
            </Button>
          </div>
        </FormField>

        <FormGroup>
          <FormField label="Quantidade" required>
            <Input
              type="number"
              v-model.number="itemForm.quantidade"
              min="1"
            />
          </FormField>

          <FormField label="Valor Unitario (R$)" required>
            <Input
              type="number"
              v-model.number="itemForm.valor_unitario"
              min="0"
              step="0.01"
            />
          </FormField>
        </FormGroup>

        <!-- Subtotal calculado -->
        <div class="rounded-lg bg-muted p-4 space-y-2">
          <div class="flex justify-between items-center text-sm">
            <span class="text-muted-foreground">
              {{ itemForm.quantidade }} x {{ formatCurrency(Number(itemForm.valor_unitario)) }}/{{ itemForm.periodo_aluguel === 'mensal' ? 'mes' : 'dia' }}
            </span>
            <span class="font-medium">
              {{ formatCurrency(Number(itemForm.quantidade) * Number(itemForm.valor_unitario)) }}/{{ itemForm.periodo_aluguel === 'mensal' ? 'mes' : 'dia' }}
            </span>
          </div>
          <div class="flex justify-between items-center pt-2 border-t">
            <span class="text-sm text-muted-foreground">
              Total ({{ itemForm.periodo_aluguel === 'mensal' ? Math.ceil(diasLocacao / 30) + ' mes(es)' : diasLocacao + ' dias' }})
            </span>
            <span class="text-lg font-semibold">
              {{ formatCurrency(subtotalItem) }}
            </span>
          </div>
        </div>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="showItemDialog = false"
          >
            Cancelar
          </Button>
          <Button type="submit" :disabled="isSubmittingItem">
            <Spinner v-if="isSubmittingItem" size="sm" class="mr-2" />
            {{ editingItem ? "Salvar" : "Adicionar" }}
          </Button>
        </DialogFooter>
      </form>
    </Dialog>

    <!-- Dialog Criar Ativo -->
    <Dialog
      v-model:open="showTipoAtivoDialog"
      title="Novo Ativo"
      description="Cadastre um novo ativo rapidamente"
    >
      <form @submit="tipoAtivoForm.handleSubmit" class="space-y-4">
        <FormField label="Nome" :error="tipoAtivoForm.getError('nome')" required>
          <Input
            v-model="tipoAtivoForm.values.nome"
            placeholder="Ex: Cadeira, Mesa, Tenda"
            :error="tipoAtivoForm.hasError('nome')"
          />
        </FormField>

        <FormField label="Descricao" :error="tipoAtivoForm.getError('descricao')">
          <Input
            v-model="tipoAtivoForm.values.descricao"
            placeholder="Descricao do ativo"
          />
        </FormField>

        <FormGroup>
          <FormField label="Unidade de Medida" :error="tipoAtivoForm.getError('unidade_medida')" required>
            <Input
              v-model="tipoAtivoForm.values.unidade_medida"
              placeholder="un, m, m2, etc"
              :error="tipoAtivoForm.hasError('unidade_medida')"
            />
          </FormField>

          <FormField label="Valor Mensal Sugerido (R$)" :error="tipoAtivoForm.getError('valor_mensal_sugerido')">
            <Input
              type="number"
              v-model.number="tipoAtivoForm.values.valor_mensal_sugerido"
              min="0"
              step="0.01"
            />
          </FormField>
        </FormGroup>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="showTipoAtivoDialog = false"
          >
            Cancelar
          </Button>
          <Button type="submit" :disabled="tipoAtivoForm.isSubmitting.value">
            <Spinner v-if="tipoAtivoForm.isSubmitting.value" size="sm" class="mr-2" />
            Salvar
          </Button>
        </DialogFooter>
      </form>
    </Dialog>

    <!-- Dialog Editar Contrato -->
    <Dialog
      v-model:open="showEditDialog"
      title="Editar Contrato"
      description="Altere os dados do contrato"
    >
      <form @submit.prevent="submitEditContrato" class="space-y-4">
        <FormField label="Locatario" required>
          <Combobox
            v-model="editForm.locatario_id"
            :options="locatarios"
            placeholder="Selecione o locatario..."
            search-placeholder="Buscar locatario..."
            empty-text="Nenhum locatario encontrado"
          />
        </FormField>

        <FormGroup>
          <FormField label="Data de Inicio" required>
            <Input
              type="date"
              v-model="editForm.data_inicio"
            />
          </FormField>

          <FormField label="Data de Termino" required>
            <Input
              type="date"
              v-model="editForm.data_termino"
            />
          </FormField>
        </FormGroup>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="showEditDialog = false"
          >
            Cancelar
          </Button>
          <Button type="submit" :disabled="isSubmittingEdit">
            <Spinner v-if="isSubmittingEdit" size="sm" class="mr-2" />
            Salvar
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
        <Button
          variant="outline"
          @click="showConfirmDialog = false"
        >
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

    <!-- Dialog Tipo de Cobranca -->
    <Dialog
      v-model:open="showTipoCobrancaDialog"
      title="Tipo de Cobranca"
      description="Defina como sera feita a cobranca deste contrato"
    >
      <form @submit.prevent="submitTipoCobranca" class="space-y-4">
        <FormField label="Tipo de Cobranca" required>
          <Select
            v-model="tipoCobrancaForm"
            :options="tiposCobranca"
            placeholder="Selecione o tipo..."
          />
        </FormField>

        <div class="bg-muted rounded-lg p-4 space-y-2 text-sm">
          <p class="font-medium">O que significa cada opcao:</p>
          <ul class="space-y-1 text-muted-foreground">
            <li><strong>Sem cobranca:</strong> Nao usa o sistema de pagamentos</li>
            <li><strong>Antecipado:</strong> Pagamento deve ser feito antes de ativar o contrato</li>
            <li><strong>Recorrente Stripe:</strong> Cobranca automatica mensal via Stripe</li>
            <li><strong>Recorrente Manual:</strong> Voce registra os pagamentos manualmente</li>
          </ul>
        </div>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="showTipoCobrancaDialog = false"
          >
            Cancelar
          </Button>
          <Button type="submit" :disabled="isSubmittingTipoCobranca">
            <Spinner v-if="isSubmittingTipoCobranca" size="sm" class="mr-2" />
            Salvar
          </Button>
        </DialogFooter>
      </form>
    </Dialog>

    <!-- Dialog Erro de Estoque -->
    <Dialog
      v-model:open="showEstoqueErrorDialog"
      title="Estoque Insuficiente"
    >
      <div v-if="estoqueError" class="space-y-4">
        <!-- Icone e mensagem principal -->
        <div class="flex items-start gap-4">
          <div class="rounded-full bg-destructive/10 p-3">
            <AlertTriangle class="size-6 text-destructive" />
          </div>
          <div class="flex-1">
            <p class="font-medium">{{ estoqueError.message }}</p>
          </div>
        </div>

        <!-- Detalhes -->
        <div class="rounded-lg bg-muted p-4 space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-muted-foreground">Ativo:</span>
            <span class="font-medium">{{ estoqueError.tipoAtivo }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-muted-foreground">Quantidade necessaria:</span>
            <span class="font-medium">{{ estoqueError.quantidadeSolicitada }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-muted-foreground">Quantidade disponivel:</span>
            <span class="font-medium">{{ estoqueError.quantidadeDisponivel }}</span>
          </div>
          <div class="flex justify-between text-sm border-t pt-2">
            <span class="text-muted-foreground">Quantidade faltante:</span>
            <span class="font-semibold text-destructive">{{ estoqueError.quantidadeFaltante }}</span>
          </div>
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            @click="showEstoqueErrorDialog = false"
          >
            Fechar
          </Button>
          <Button
            @click="router.push({ name: 'tipos-ativos.edit', params: { id: estoqueError.tipoAtivoId } }); showEstoqueErrorDialog = false"
          >
            <Package class="size-4 mr-2" />
            Criar Lote
          </Button>
        </DialogFooter>
      </div>
    </Dialog>
  </div>
</template>
