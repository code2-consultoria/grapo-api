<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import { RouterLink } from "vue-router"
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Input } from "@/components/ui/input"
import { Dialog, DialogFooter } from "@/components/ui/dialog"
import { FormField } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import {
  CreditCard,
  CheckCircle,
  XCircle,
  AlertTriangle,
  Calendar,
} from "lucide-vue-next"
import api from "@/lib/api"

interface Props {
  contratoId: string
  contratoStatus: string
}

interface PagamentoStatus {
  has_stripe_payment: boolean
  dia_vencimento?: number
}

interface ConnectStatus {
  has_account: boolean
  onboarding_complete: boolean
  charges_enabled: boolean
  payouts_enabled: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: "updated"): void
}>()

const pagamentoStatus = ref<PagamentoStatus | null>(null)
const connectStatus = ref<ConnectStatus | null>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)

// Dialog de ativacao
const showActivateDialog = ref(false)
const diaVencimento = ref(10)
const isActivating = ref(false)

// Dialog de cancelamento
const showCancelDialog = ref(false)
const isCanceling = ref(false)

// Verifica se locador pode receber pagamentos
const locadorPronto = computed(() => {
  return connectStatus.value?.has_account &&
    connectStatus.value?.onboarding_complete &&
    connectStatus.value?.charges_enabled
})

// Carrega status
async function loadStatus() {
  try {
    isLoading.value = true
    error.value = null

    const [pagamentoRes, connectRes] = await Promise.all([
      api.get<{ data: PagamentoStatus }>(`/contratos/${props.contratoId}/pagamento-stripe`),
      api.get<{ data: ConnectStatus }>("/stripe/connect/status"),
    ])

    pagamentoStatus.value = pagamentoRes.data
    connectStatus.value = connectRes.data
  } catch (err: any) {
    error.value = err.message || "Erro ao carregar status"
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

// Ativa pagamento
async function activatePayment() {
  try {
    isActivating.value = true
    error.value = null

    await api.post(`/contratos/${props.contratoId}/pagamento-stripe`, {
      dia_vencimento: diaVencimento.value,
    })

    showActivateDialog.value = false
    emit("updated")
    await loadStatus()
  } catch (err: any) {
    error.value = err.message || "Erro ao ativar pagamento"
    console.error(err)
  } finally {
    isActivating.value = false
  }
}

// Cancela pagamento
async function cancelPayment() {
  try {
    isCanceling.value = true
    error.value = null

    await api.delete(`/contratos/${props.contratoId}/pagamento-stripe`)

    showCancelDialog.value = false
    emit("updated")
    await loadStatus()
  } catch (err: any) {
    error.value = err.message || "Erro ao cancelar pagamento"
    console.error(err)
  } finally {
    isCanceling.value = false
  }
}

onMounted(() => {
  loadStatus()
})
</script>

<template>
  <Card>
    <CardHeader>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div
            class="size-10 rounded-full flex items-center justify-center"
            :class="{
              'bg-muted': !pagamentoStatus?.has_stripe_payment,
              'bg-green-100': pagamentoStatus?.has_stripe_payment,
            }"
          >
            <CreditCard
              class="size-5"
              :class="{
                'text-muted-foreground': !pagamentoStatus?.has_stripe_payment,
                'text-green-600': pagamentoStatus?.has_stripe_payment,
              }"
            />
          </div>
          <div>
            <CardTitle class="text-base">Pagamento via Stripe</CardTitle>
            <CardDescription>
              <template v-if="pagamentoStatus?.has_stripe_payment">
                Cobranca recorrente ativa
              </template>
              <template v-else>
                Configure cobranca automatica para o locatario
              </template>
            </CardDescription>
          </div>
        </div>
        <Badge
          v-if="pagamentoStatus"
          :variant="pagamentoStatus.has_stripe_payment ? 'default' : 'secondary'"
        >
          {{ pagamentoStatus.has_stripe_payment ? "Ativo" : "Inativo" }}
        </Badge>
      </div>
    </CardHeader>
    <CardContent>
      <!-- Loading -->
      <div v-if="isLoading" class="flex items-center justify-center py-4">
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
        <!-- Se nao tem Stripe Connect -->
        <div
          v-if="!locadorPronto"
          class="bg-amber-50 border border-amber-200 rounded-lg p-4 space-y-3"
        >
          <div class="flex items-start gap-3">
            <AlertTriangle class="size-5 text-amber-600 mt-0.5" />
            <div>
              <p class="font-medium text-amber-800">
                Configure seu Stripe Connect primeiro
              </p>
              <p class="text-sm text-amber-700">
                Para receber pagamentos dos locatarios, voce precisa configurar sua conta Stripe Connect.
              </p>
            </div>
          </div>
          <RouterLink :to="{ name: 'perfil' }">
            <Button variant="outline" size="sm" class="gap-2">
              <CreditCard class="size-4" />
              Configurar Stripe Connect
            </Button>
          </RouterLink>
        </div>

        <!-- Se tem pagamento ativo -->
        <div v-else-if="pagamentoStatus?.has_stripe_payment" class="space-y-4">
          <div class="flex items-center gap-4 p-3 bg-green-50 rounded-lg">
            <CheckCircle class="size-5 text-green-600" />
            <div class="flex-1">
              <p class="font-medium text-green-800">Cobranca automatica ativa</p>
              <p class="text-sm text-green-700">
                O locatario recebera faturas todo dia {{ pagamentoStatus.dia_vencimento }}
              </p>
            </div>
            <div class="flex items-center gap-2 text-green-700">
              <Calendar class="size-4" />
              <span class="font-medium">Dia {{ pagamentoStatus.dia_vencimento }}</span>
            </div>
          </div>

          <Button
            v-if="props.contratoStatus === 'ativo'"
            variant="destructive"
            size="sm"
            @click="showCancelDialog = true"
          >
            <XCircle class="size-4 mr-2" />
            Desativar Pagamento
          </Button>
        </div>

        <!-- Se pode ativar -->
        <div v-else-if="props.contratoStatus === 'ativo'" class="space-y-3">
          <p class="text-sm text-muted-foreground">
            Ative o pagamento automatico para que o locatario receba faturas mensais via Stripe.
            Voce recebera 100% do valor diretamente na sua conta.
          </p>
          <Button @click="showActivateDialog = true" class="gap-2">
            <CreditCard class="size-4" />
            Ativar Pagamento Automatico
          </Button>
        </div>

        <!-- Se contrato nao esta ativo -->
        <div v-else class="text-sm text-muted-foreground">
          O pagamento automatico so pode ser configurado em contratos ativos.
        </div>
      </template>
    </CardContent>
  </Card>

  <!-- Dialog Ativar -->
  <Dialog
    v-model:open="showActivateDialog"
    title="Ativar Pagamento Automatico"
    description="Configure a cobranca recorrente para este contrato"
  >
    <form @submit.prevent="activatePayment" class="space-y-4">
      <FormField label="Dia de Vencimento" required>
        <div class="space-y-2">
          <Input
            type="number"
            v-model.number="diaVencimento"
            min="1"
            max="28"
            placeholder="1-28"
          />
          <p class="text-xs text-muted-foreground">
            O locatario recebera a fatura todo mes neste dia (entre 1 e 28)
          </p>
        </div>
      </FormField>

      <div class="bg-muted rounded-lg p-4 space-y-2 text-sm">
        <p class="font-medium">O que acontece ao ativar:</p>
        <ul class="list-disc list-inside space-y-1 text-muted-foreground">
          <li>Uma assinatura recorrente sera criada no Stripe</li>
          <li>O locatario recebera um email com link para pagamento</li>
          <li>Cobracas serao feitas automaticamente todo mes</li>
          <li>Voce recebe 100% do valor na sua conta Stripe</li>
        </ul>
      </div>

      <DialogFooter>
        <Button
          type="button"
          variant="outline"
          @click="showActivateDialog = false"
        >
          Cancelar
        </Button>
        <Button type="submit" :disabled="isActivating">
          <Spinner v-if="isActivating" size="sm" class="mr-2" />
          Ativar Pagamento
        </Button>
      </DialogFooter>
    </form>
  </Dialog>

  <!-- Dialog Cancelar -->
  <Dialog
    v-model:open="showCancelDialog"
    title="Desativar Pagamento"
    description="Tem certeza que deseja desativar o pagamento automatico?"
  >
    <div class="space-y-4">
      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <AlertTriangle class="size-5 text-amber-600 mt-0.5" />
          <div class="text-sm">
            <p class="font-medium text-amber-800">Atencao</p>
            <p class="text-amber-700">
              A assinatura sera cancelada no Stripe e o locatario nao recebera mais faturas automaticas.
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
          @click="cancelPayment"
        >
          <Spinner v-if="isCanceling" size="sm" class="mr-2" />
          Desativar
        </Button>
      </DialogFooter>
    </div>
  </Dialog>
</template>
