<script setup lang="ts">
import { ref, computed } from "vue"
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Dialog, DialogFooter } from "@/components/ui/dialog"
import { Spinner } from "@/components/ui/spinner"
import {
  CreditCard,
  ExternalLink,
  AlertTriangle,
  CheckCircle,
  Clock,
} from "lucide-vue-next"
import { useNotification } from "@/composables"
import { RouterLink } from "vue-router"
import api from "@/lib/api"

interface Props {
  contratoId: string
  contratoStatus: string
  tipoCobranca: string
  valorTotal: number
}

interface ConnectStatus {
  has_account: boolean
  onboarding_complete: boolean
  charges_enabled: boolean
  payouts_enabled: boolean
}

const props = defineProps<Props>()

const _emit = defineEmits<{
  (e: "updated"): void
}>()

const { success: _success, error: showError } = useNotification()

// Estado
const connectStatus = ref<ConnectStatus | null>(null)
const isLoadingConnect = ref(true)
const isCreatingCheckout = ref(false)

// Dialog de checkout
const showCheckoutDialog = ref(false)
const checkoutUrl = ref<string | null>(null)

// Computed
const isAntecipado = computed(() => {
  return ["antecipado_stripe", "antecipado_pix"].includes(props.tipoCobranca)
})

const isAguardandoPagamento = computed(() => {
  return props.contratoStatus === "aguardando_pagamento"
})

const exibeComponente = computed(() => {
  return isAntecipado.value && isAguardandoPagamento.value
})

const locadorPronto = computed(() => {
  return connectStatus.value?.has_account &&
    connectStatus.value?.onboarding_complete &&
    connectStatus.value?.charges_enabled
})

const tipoCobrancaLabel = computed(() => {
  switch (props.tipoCobranca) {
    case "antecipado_stripe":
      return "Cartao de Credito"
    case "antecipado_pix":
      return "PIX"
    default:
      return props.tipoCobranca
  }
})

// Carrega status do Connect
async function loadConnectStatus() {
  try {
    isLoadingConnect.value = true
    const response = await api.get<{ data: ConnectStatus }>("/stripe/connect/status")
    connectStatus.value = response.data
  } catch {
    // Silently fail
  } finally {
    isLoadingConnect.value = false
  }
}

// Cria checkout
async function createCheckout() {
  try {
    isCreatingCheckout.value = true

    const currentUrl = window.location.href
    const response = await api.post<{ checkout_url: string; checkout_id: string }>(
      `/contratos/${props.contratoId}/checkout`,
      {
        success_url: currentUrl,
        cancel_url: currentUrl,
      },
    )

    checkoutUrl.value = response.checkout_url
    showCheckoutDialog.value = true
  } catch (err: unknown) {
    const apiError = err as { message?: string }
    showError("Erro", apiError.message || "Erro ao criar checkout")
  } finally {
    isCreatingCheckout.value = false
  }
}

// Abre checkout em nova aba
function openCheckout() {
  if (checkoutUrl.value) {
    window.open(checkoutUrl.value, "_blank")
  }
  showCheckoutDialog.value = false
}

// Formata moeda
function formatCurrency(value: number): string {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value)
}

// Carrega ao montar se for antecipado
if (isAntecipado.value) {
  loadConnectStatus()
}
</script>

<template>
  <Card v-if="exibeComponente">
    <CardHeader>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="size-10 rounded-full flex items-center justify-center bg-amber-100">
            <Clock class="size-5 text-amber-600" />
          </div>
          <div>
            <CardTitle class="text-base">Pagamento Antecipado</CardTitle>
            <CardDescription>
              Este contrato exige pagamento antes da ativacao
            </CardDescription>
          </div>
        </div>
        <Badge variant="warning">Aguardando Pagamento</Badge>
      </div>
    </CardHeader>
    <CardContent>
      <!-- Loading -->
      <div v-if="isLoadingConnect" class="flex items-center justify-center py-4">
        <Spinner />
      </div>

      <!-- Se nao tem Stripe Connect -->
      <div
        v-else-if="!locadorPronto"
        class="bg-amber-50 border border-amber-200 rounded-lg p-4 space-y-3"
      >
        <div class="flex items-start gap-3">
          <AlertTriangle class="size-5 text-amber-600 mt-0.5" />
          <div>
            <p class="font-medium text-amber-800">
              Configure seu Stripe Connect primeiro
            </p>
            <p class="text-sm text-amber-700">
              Para receber pagamentos antecipados, voce precisa configurar sua conta Stripe Connect.
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

      <!-- Checkout disponivel -->
      <div v-else class="space-y-4">
        <div class="bg-muted rounded-lg p-4 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-sm text-muted-foreground">Valor a pagar</span>
            <span class="text-xl font-semibold">{{ formatCurrency(valorTotal) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-muted-foreground">Metodo</span>
            <Badge variant="outline">{{ tipoCobrancaLabel }}</Badge>
          </div>
        </div>

        <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <CreditCard class="size-5 text-blue-600 mt-0.5" />
          <div class="text-sm">
            <p class="font-medium text-blue-800">Pagamento via Stripe</p>
            <p class="text-blue-700">
              Ao clicar em "Realizar Pagamento", voce sera redirecionado para a pagina segura do Stripe.
              Apos o pagamento, o contrato sera ativado automaticamente.
            </p>
          </div>
        </div>

        <Button
          class="w-full gap-2"
          size="lg"
          :disabled="isCreatingCheckout"
          @click="createCheckout"
        >
          <Spinner v-if="isCreatingCheckout" size="sm" />
          <CreditCard v-else class="size-5" />
          Realizar Pagamento
        </Button>
      </div>
    </CardContent>
  </Card>

  <!-- Dialog de checkout criado -->
  <Dialog
    v-model:open="showCheckoutDialog"
    title="Checkout Criado"
    description="O link de pagamento foi gerado com sucesso"
  >
    <div class="space-y-4">
      <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <CheckCircle class="size-5 text-green-600 mt-0.5" />
          <div class="text-sm">
            <p class="font-medium text-green-800">Link de pagamento pronto!</p>
            <p class="text-green-700">
              Clique no botao abaixo para abrir a pagina de pagamento do Stripe.
              Apos a conclusao, o contrato sera ativado automaticamente.
            </p>
          </div>
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          @click="showCheckoutDialog = false"
        >
          Fechar
        </Button>
        <Button @click="openCheckout" class="gap-2">
          <ExternalLink class="size-4" />
          Abrir Pagamento
        </Button>
      </DialogFooter>
    </div>
  </Dialog>
</template>
