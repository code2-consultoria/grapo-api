<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue"
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { Dialog, DialogFooter } from "@/components/ui/dialog"
import { FormField, FormGroup } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
  TableEmpty,
} from "@/components/ui/table"
import {
  Plus,
  CheckCircle,
  XCircle,
  AlertTriangle,
  Clock,
  Receipt,
} from "lucide-vue-next"
import { useNotification } from "@/composables"
import api from "@/lib/api"
import type { Pagamento, PagamentoResumo, OrigemPagamento } from "@/types"

interface Props {
  contratoId: string
  contratoStatus: string
  tipoCobranca: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: "updated"): void
}>()

const { success, error: showError } = useNotification()

// Estado
const pagamentos = ref<Pagamento[]>([])
const resumo = ref<PagamentoResumo | null>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)

// Dialog adicionar fatura
const showAddDialog = ref(false)
const addForm = ref({
  valor: 0,
  desconto_comercial: 0,
  data_vencimento: "",
  data_pagamento: "",
  origem: "manual" as OrigemPagamento,
  observacoes: "",
  ja_paga: false,
})
const isAdding = ref(false)

// Quando ja_paga muda, limpa ou preenche data_pagamento
watch(() => addForm.value.ja_paga, (jaPaga) => {
  if (jaPaga && !addForm.value.data_pagamento) {
    addForm.value.data_pagamento = new Date().toISOString().split("T")[0]
  } else if (!jaPaga) {
    addForm.value.data_pagamento = ""
  }
})

// Dialog marcar como pago
const showPayDialog = ref(false)
const payingPagamento = ref<Pagamento | null>(null)
const payForm = ref({
  data_pagamento: new Date().toISOString().split("T")[0],
  observacoes: "",
})
const isPaying = ref(false)

// Dialog cancelar
const showCancelDialog = ref(false)
const cancelingPagamento = ref<Pagamento | null>(null)
const isCanceling = ref(false)

// Computed
const podeGerenciarPagamentos = computed(() => {
  return ["ativo", "aguardando_pagamento"].includes(props.contratoStatus)
})

const exibeComponente = computed(() => {
  // Mostra se o contrato tem cobranca manual ou recorrente manual
  return ["recorrente_manual", "sem_cobranca"].includes(props.tipoCobranca) ||
    // Ou se ja tem pagamentos registrados
    pagamentos.value.length > 0
})

// Carrega dados
async function loadData() {
  try {
    isLoading.value = true
    error.value = null

    const [pagamentosRes, resumoRes] = await Promise.all([
      api.get<{ data: Pagamento[] }>(`/contratos/${props.contratoId}/pagamentos`),
      api.get<{ data: PagamentoResumo }>(`/contratos/${props.contratoId}/pagamentos/resumo`),
    ])

    pagamentos.value = pagamentosRes.data
    resumo.value = resumoRes.data
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    error.value = apiError.message || "Erro ao carregar faturas"
  } finally {
    isLoading.value = false
  }
}

// Adiciona fatura
function openAddDialog() {
  addForm.value = {
    valor: 0,
    desconto_comercial: 0,
    data_vencimento: "",
    data_pagamento: "",
    origem: "manual",
    observacoes: "",
    ja_paga: false,
  }
  showAddDialog.value = true
}

async function submitAdd() {
  try {
    isAdding.value = true

    const payload = {
      valor: addForm.value.valor,
      desconto_comercial: addForm.value.desconto_comercial || 0,
      data_vencimento: addForm.value.data_vencimento,
      origem: addForm.value.origem,
      observacoes: addForm.value.observacoes || null,
      ...(addForm.value.data_pagamento ? { data_pagamento: addForm.value.data_pagamento } : {}),
    }

    await api.post(`/contratos/${props.contratoId}/pagamentos`, payload)

    success("Sucesso!", "Fatura registrada")
    showAddDialog.value = false
    emit("updated")
    await loadData()
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    showError("Erro", apiError.message || "Erro ao registrar fatura")
  } finally {
    isAdding.value = false
  }
}

// Marca como pago
function openPayDialog(pagamento: Pagamento) {
  payingPagamento.value = pagamento
  payForm.value = {
    data_pagamento: new Date().toISOString().split("T")[0],
    observacoes: "",
  }
  showPayDialog.value = true
}

async function submitPay() {
  if (!payingPagamento.value) return

  try {
    isPaying.value = true

    await api.post(
      `/contratos/${props.contratoId}/pagamentos/${payingPagamento.value.id}/pagar`,
      payForm.value,
    )

    success("Sucesso!", "Pagamento da fatura confirmado")
    showPayDialog.value = false
    emit("updated")
    await loadData()
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    showError("Erro", apiError.message || "Erro ao confirmar pagamento da fatura")
  } finally {
    isPaying.value = false
  }
}

// Cancela pagamento
function openCancelDialog(pagamento: Pagamento) {
  cancelingPagamento.value = pagamento
  showCancelDialog.value = true
}

async function submitCancel() {
  if (!cancelingPagamento.value) return

  try {
    isCanceling.value = true

    await api.delete(
      `/contratos/${props.contratoId}/pagamentos/${cancelingPagamento.value.id}`,
    )

    success("Sucesso!", "Fatura cancelada")
    showCancelDialog.value = false
    emit("updated")
    await loadData()
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    showError("Erro", apiError.message || "Erro ao cancelar fatura")
  } finally {
    isCanceling.value = false
  }
}

// Formatadores
function formatCurrency(value: string | number): string {
  const num = typeof value === "string" ? parseFloat(value) : value
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(num)
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString("pt-BR")
}

function getStatusVariant(status: string): "default" | "success" | "secondary" | "destructive" | "warning" {
  switch (status) {
    case "pago":
      return "success"
    case "pendente":
      return "secondary"
    case "atrasado":
      return "warning"
    case "cancelado":
      return "destructive"
    default:
      return "default"
  }
}

function getStatusIcon(status: string) {
  switch (status) {
    case "pago":
      return CheckCircle
    case "pendente":
      return Clock
    case "atrasado":
      return AlertTriangle
    case "cancelado":
      return XCircle
    default:
      return Receipt
  }
}

onMounted(() => {
  loadData()
})

// Expoe metodo para recarregar
defineExpose({ loadData })
</script>

<template>
  <Card v-if="exibeComponente || isLoading">
    <CardHeader class="flex flex-row items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="size-10 rounded-full flex items-center justify-center bg-primary/10">
          <Receipt class="size-5 text-primary" />
        </div>
        <div>
          <CardTitle class="text-base">Faturas</CardTitle>
          <CardDescription>
            Historico e gestao de faturas do contrato
          </CardDescription>
        </div>
      </div>
      <Button
        v-if="podeGerenciarPagamentos"
        size="sm"
        @click="openAddDialog"
      >
        <Plus class="size-4 mr-2" />
        Registrar Fatura
      </Button>
    </CardHeader>
    <CardContent>
      <!-- Loading -->
      <div v-if="isLoading" class="flex items-center justify-center py-8">
        <Spinner />
      </div>

      <!-- Erro -->
      <div
        v-else-if="error"
        class="bg-destructive/10 text-destructive rounded-lg p-3 text-sm flex items-center gap-2"
      >
        <AlertTriangle class="size-4" />
        {{ error }}
      </div>

      <!-- Conteudo -->
      <template v-else>
        <!-- Resumo -->
        <div v-if="resumo" class="grid gap-4 md:grid-cols-4 mb-6">
          <div class="bg-muted rounded-lg p-4">
            <p class="text-sm text-muted-foreground">Total Contrato</p>
            <p class="text-lg font-semibold">{{ formatCurrency(resumo.total_contrato) }}</p>
          </div>
          <div class="bg-green-50 rounded-lg p-4">
            <p class="text-sm text-green-700">Total Pago</p>
            <p class="text-lg font-semibold text-green-800">
              {{ formatCurrency(resumo.total_pago) }}
            </p>
            <p class="text-xs text-green-600">{{ resumo.qtd_pagos }} pagamento(s)</p>
          </div>
          <div class="bg-amber-50 rounded-lg p-4">
            <p class="text-sm text-amber-700">Total Pendente</p>
            <p class="text-lg font-semibold text-amber-800">
              {{ formatCurrency(resumo.total_pendente) }}
            </p>
            <p class="text-xs text-amber-600">{{ resumo.qtd_pendentes }} pendente(s)</p>
          </div>
          <div v-if="parseFloat(resumo.total_atrasado) > 0" class="bg-red-50 rounded-lg p-4">
            <p class="text-sm text-red-700">Total Atrasado</p>
            <p class="text-lg font-semibold text-red-800">
              {{ formatCurrency(resumo.total_atrasado) }}
            </p>
            <p class="text-xs text-red-600">{{ resumo.qtd_atrasados }} atrasado(s)</p>
          </div>
        </div>

        <!-- Tabela de pagamentos -->
        <div class="rounded-md border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Vencimento</TableHead>
                <TableHead>Valor</TableHead>
                <TableHead>Desconto</TableHead>
                <TableHead>Valor Final</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Origem</TableHead>
                <TableHead>Pago em</TableHead>
                <TableHead v-if="podeGerenciarPagamentos" class="w-[120px]">Acoes</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableEmpty
                v-if="pagamentos.length === 0"
                :colspan="podeGerenciarPagamentos ? 8 : 7"
                message="Nenhuma fatura registrada"
              />
              <TableRow v-else v-for="pagamento in pagamentos" :key="pagamento.id">
                <TableCell>{{ formatDate(pagamento.data_vencimento) }}</TableCell>
                <TableCell>{{ formatCurrency(pagamento.valor) }}</TableCell>
                <TableCell>
                  <span v-if="parseFloat(pagamento.desconto_comercial) > 0" class="text-green-600">
                    -{{ formatCurrency(pagamento.desconto_comercial) }}
                  </span>
                  <span v-else class="text-muted-foreground">-</span>
                </TableCell>
                <TableCell class="font-medium">{{ formatCurrency(pagamento.valor_final) }}</TableCell>
                <TableCell>
                  <Badge :variant="getStatusVariant(pagamento.status)" class="gap-1">
                    <component :is="getStatusIcon(pagamento.status)" class="size-3" />
                    {{ pagamento.status_label }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <Badge variant="outline">{{ pagamento.origem_label }}</Badge>
                </TableCell>
                <TableCell>
                  {{ pagamento.data_pagamento ? formatDate(pagamento.data_pagamento) : "-" }}
                </TableCell>
                <TableCell v-if="podeGerenciarPagamentos">
                  <div class="flex items-center gap-1">
                    <Button
                      v-if="pagamento.status === 'pendente'"
                      variant="ghost"
                      size="icon-sm"
                      title="Marcar como pago"
                      @click="openPayDialog(pagamento)"
                    >
                      <CheckCircle class="size-4 text-green-600" />
                    </Button>
                    <Button
                      v-if="pagamento.status === 'pendente'"
                      variant="ghost"
                      size="icon-sm"
                      title="Cancelar"
                      @click="openCancelDialog(pagamento)"
                    >
                      <XCircle class="size-4 text-destructive" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </template>
    </CardContent>
  </Card>

  <!-- Dialog Adicionar Fatura -->
  <Dialog
    v-model:open="showAddDialog"
    title="Registrar Fatura"
    description="Registre uma fatura do contrato"
  >
    <form @submit.prevent="submitAdd" class="space-y-4">
      <FormGroup>
        <FormField label="Valor (R$)" required>
          <Input
            type="number"
            v-model.number="addForm.valor"
            min="0.01"
            step="0.01"
            placeholder="0,00"
          />
        </FormField>

        <FormField label="Desconto Comercial (R$)">
          <Input
            type="number"
            v-model.number="addForm.desconto_comercial"
            min="0"
            :max="addForm.valor"
            step="0.01"
            placeholder="0,00"
          />
          <p class="text-xs text-muted-foreground mt-1">
            Maximo: {{ formatCurrency(addForm.valor) }}
          </p>
        </FormField>
      </FormGroup>

      <FormField label="Data de Vencimento" required>
        <Input
          type="date"
          v-model="addForm.data_vencimento"
        />
      </FormField>

      <FormGroup>
        <FormField label="Origem" required>
          <div class="flex gap-2">
            <Button
              type="button"
              size="sm"
              :variant="addForm.origem === 'manual' ? 'default' : 'outline'"
              @click="addForm.origem = 'manual'"
            >
              Manual
            </Button>
            <Button
              type="button"
              size="sm"
              :variant="addForm.origem === 'pix' ? 'default' : 'outline'"
              @click="addForm.origem = 'pix'"
            >
              PIX
            </Button>
          </div>
        </FormField>

        <FormField label="Fatura ja paga?">
          <div class="flex gap-2">
            <Button
              type="button"
              size="sm"
              :variant="!addForm.ja_paga ? 'default' : 'outline'"
              @click="addForm.ja_paga = false"
            >
              Nao
            </Button>
            <Button
              type="button"
              size="sm"
              :variant="addForm.ja_paga ? 'default' : 'outline'"
              @click="addForm.ja_paga = true"
            >
              Sim
            </Button>
          </div>
        </FormField>
      </FormGroup>

      <!-- Data de Pagamento (aparece quando ja_paga = true) -->
      <FormField v-if="addForm.ja_paga" label="Data de Pagamento" required>
        <Input
          type="date"
          v-model="addForm.data_pagamento"
        />
      </FormField>

      <FormField label="Observacoes">
        <Textarea
          v-model="addForm.observacoes"
          placeholder="Informacoes adicionais sobre a fatura..."
          rows="2"
        />
      </FormField>

      <DialogFooter>
        <Button
          type="button"
          variant="outline"
          @click="showAddDialog = false"
        >
          Cancelar
        </Button>
        <Button type="submit" :disabled="isAdding">
          <Spinner v-if="isAdding" size="sm" class="mr-2" />
          Registrar
        </Button>
      </DialogFooter>
    </form>
  </Dialog>

  <!-- Dialog Marcar como Pago -->
  <Dialog
    v-model:open="showPayDialog"
    title="Confirmar Pagamento da Fatura"
    :description="`Confirmar recebimento de ${payingPagamento ? formatCurrency(payingPagamento.valor) : ''}`"
  >
    <form @submit.prevent="submitPay" class="space-y-4">
      <FormField label="Data de Pagamento" required>
        <Input
          type="date"
          v-model="payForm.data_pagamento"
        />
      </FormField>

      <FormField label="Observacoes">
        <Textarea
          v-model="payForm.observacoes"
          placeholder="Ex: Pago via PIX..."
          rows="2"
        />
      </FormField>

      <DialogFooter>
        <Button
          type="button"
          variant="outline"
          @click="showPayDialog = false"
        >
          Cancelar
        </Button>
        <Button type="submit" :disabled="isPaying">
          <Spinner v-if="isPaying" size="sm" class="mr-2" />
          Confirmar Pagamento
        </Button>
      </DialogFooter>
    </form>
  </Dialog>

  <!-- Dialog Cancelar -->
  <Dialog
    v-model:open="showCancelDialog"
    title="Cancelar Fatura"
    :description="`Deseja cancelar a fatura de ${cancelingPagamento ? formatCurrency(cancelingPagamento.valor) : ''}?`"
  >
    <div class="space-y-4">
      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <AlertTriangle class="size-5 text-amber-600 mt-0.5" />
          <div class="text-sm">
            <p class="font-medium text-amber-800">Atencao</p>
            <p class="text-amber-700">
              Esta acao nao pode ser desfeita. A fatura sera marcada como cancelada.
            </p>
          </div>
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          @click="showCancelDialog = false"
        >
          Voltar
        </Button>
        <Button
          variant="destructive"
          :disabled="isCanceling"
          @click="submitCancel"
        >
          <Spinner v-if="isCanceling" size="sm" class="mr-2" />
          Cancelar Fatura
        </Button>
      </DialogFooter>
    </div>
  </Dialog>
</template>
