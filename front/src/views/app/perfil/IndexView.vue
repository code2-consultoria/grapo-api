<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import { useAuth, useNotification } from "@/composables"
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Spinner } from "@/components/ui/spinner"
import { Dialog, DialogFooter } from "@/components/ui/dialog"
import {
  User,
  Mail,
  CreditCard,
  CheckCircle,
  XCircle,
  ExternalLink,
  RefreshCw,
  AlertTriangle,
  Calendar,
  Crown,
  Check,
  Percent,
  Save,
} from "lucide-vue-next"
import api from "@/lib/api"

const { user } = useAuth()
const { success, error: showError } = useNotification()

interface StripeConnectStatus {
  has_account: boolean
  onboarding_complete: boolean
  charges_enabled: boolean
  payouts_enabled: boolean
  disabled_reason?: string
  pending_requirements?: string[]
  requirements_errors?: { code: string; reason: string }[]
  onboarding_url?: string
}

interface Plano {
  id: string
  nome: string
  duracao_meses: number
  valor: string
  ativo: boolean
}

interface AssinaturaStatus {
  has_subscription: boolean
  subscription: {
    id: string
    status: string
    current_period_end: string
    cancel_at_period_end: boolean
    plan: {
      id: string
      name: string
      amount: number
      interval: string
      interval_count: number
    }
  } | null
  data_limite_acesso: string | null
}

const connectStatus = ref<StripeConnectStatus | null>(null)
const isLoadingConnect = ref(true)
const isProcessing = ref(false)
const error = ref<string | null>(null)

// Assinatura
const assinaturaStatus = ref<AssinaturaStatus | null>(null)
const planos = ref<Plano[]>([])
const isLoadingAssinatura = ref(true)
const isLoadingPlanos = ref(true)
const isCancelingAssinatura = ref(false)
const isChangingPlano = ref(false)
const showCancelDialog = ref(false)
const showChangePlanoDialog = ref(false)
const selectedPlano = ref<string | null>(null)

// Majoracao
const majoracaoDiaria = ref("")
const majoracaoOriginal = ref("")
const isLoadingMajoracao = ref(true)
const isSavingMajoracao = ref(false)
const majoracaoError = ref<string | null>(null)

// Status geral da conta
const accountStatus = computed(() => {
  if (!connectStatus.value) return "loading"
  if (!connectStatus.value.has_account) return "not_configured"
  if (!connectStatus.value.onboarding_complete) return "incomplete"
  if (!connectStatus.value.charges_enabled) return "pending"
  return "active"
})

// Verifica se há pendências
const hasPendingRequirements = computed(() => {
  return (connectStatus.value?.pending_requirements?.length ?? 0) > 0
})

// Traduz motivo do bloqueio
function getDisabledReasonText(reason?: string): string {
  switch (reason) {
    case "requirements.past_due":
      return "Documentos pendentes"
    case "requirements.pending_verification":
      return "Aguardando verificação"
    case "rejected.fraud":
      return "Conta rejeitada"
    case "rejected.other":
      return "Conta rejeitada"
    default:
      return reason || "Pendência"
  }
}

// Carregar status Connect
async function loadConnectStatus() {
  try {
    isLoadingConnect.value = true
    error.value = null
    const response = await api.get<{ data: StripeConnectStatus }>("/stripe/connect/status")
    connectStatus.value = response.data
  } catch (err) {
    error.value = "Erro ao carregar status do Stripe Connect"
    console.error(err)
  } finally {
    isLoadingConnect.value = false
  }
}

// Atualizar status consultando o Stripe
async function refreshConnectStatus() {
  try {
    isLoadingConnect.value = true
    error.value = null
    const response = await api.post<{ data: StripeConnectStatus }>("/stripe/connect/refresh")
    connectStatus.value = response.data
  } catch (err: any) {
    error.value = err.message || "Erro ao atualizar status"
    console.error(err)
  } finally {
    isLoadingConnect.value = false
  }
}

// Iniciar onboarding
async function startOnboarding() {
  try {
    isProcessing.value = true
    error.value = null

    const returnUrl = `${window.location.origin}/app/perfil`
    const refreshUrl = `${window.location.origin}/app/perfil?refresh=true`

    const response = await api.post<{ onboarding_url: string }>("/stripe/connect/onboard", {
      return_url: returnUrl,
      refresh_url: refreshUrl,
    })

    window.location.href = response.onboarding_url
  } catch (err: any) {
    error.value = err.message || "Erro ao iniciar configuracao do Stripe"
    console.error(err)
  } finally {
    isProcessing.value = false
  }
}

// Continuar onboarding
async function continueOnboarding() {
  try {
    isProcessing.value = true
    error.value = null

    const returnUrl = `${window.location.origin}/app/perfil`
    const refreshUrl = `${window.location.origin}/app/perfil?refresh=true`

    const response = await api.post<{ onboarding_url: string }>("/stripe/connect/refresh", {
      return_url: returnUrl,
      refresh_url: refreshUrl,
    })

    window.location.href = response.onboarding_url
  } catch (err: any) {
    error.value = err.message || "Erro ao continuar configuracao"
    console.error(err)
  } finally {
    isProcessing.value = false
  }
}

// Abrir dashboard Stripe
async function openDashboard() {
  try {
    isProcessing.value = true
    error.value = null

    const response = await api.get<{ dashboard_url: string }>("/stripe/connect/dashboard")
    window.open(response.dashboard_url, "_blank")
  } catch (err: any) {
    error.value = err.message || "Erro ao abrir dashboard"
    console.error(err)
  } finally {
    isProcessing.value = false
  }
}

// Assinatura - Computed
const assinaturaAtiva = computed(() => {
  return assinaturaStatus.value?.has_subscription && !assinaturaStatus.value?.subscription?.cancel_at_period_end
})

const assinaturaCancelada = computed(() => {
  return assinaturaStatus.value?.subscription?.cancel_at_period_end
})

const diasRestantes = computed(() => {
  if (!assinaturaStatus.value?.data_limite_acesso) return null
  const dataLimite = new Date(assinaturaStatus.value.data_limite_acesso)
  const hoje = new Date()
  const diff = Math.ceil((dataLimite.getTime() - hoje.getTime()) / (1000 * 60 * 60 * 24))
  return diff
})

// Assinatura - Funções
async function loadAssinaturaStatus() {
  try {
    isLoadingAssinatura.value = true
    const response = await api.get<{ data: AssinaturaStatus }>("/assinaturas/status")
    assinaturaStatus.value = response.data
  } catch {
    // Silently fail
  } finally {
    isLoadingAssinatura.value = false
  }
}

async function loadPlanos() {
  try {
    isLoadingPlanos.value = true
    const response = await api.get<{ data: Plano[] }>("/planos")
    planos.value = response.data.filter((p) => p.ativo)
  } catch {
    // Silently fail
  } finally {
    isLoadingPlanos.value = false
  }
}

async function cancelarAssinatura() {
  try {
    isCancelingAssinatura.value = true
    await api.post("/assinaturas/cancelar")
    success("Assinatura cancelada", "Voce pode usar ate o fim do periodo pago.")
    showCancelDialog.value = false
    await loadAssinaturaStatus()
  } catch (err: any) {
    showError("Erro", err.message || "Erro ao cancelar assinatura")
  } finally {
    isCancelingAssinatura.value = false
  }
}

async function trocarPlano() {
  if (!selectedPlano.value) return

  try {
    isChangingPlano.value = true

    // Cancela assinatura atual e cria nova
    if (assinaturaStatus.value?.has_subscription) {
      await api.post("/assinaturas/cancelar")
    }

    // Cria checkout para novo plano
    const response = await api.post<{ checkout_url: string }>("/assinaturas/checkout", {
      plano_id: selectedPlano.value,
      success_url: `${window.location.origin}/app/perfil?assinatura=sucesso`,
      cancel_url: `${window.location.origin}/app/perfil?assinatura=cancelada`,
    })

    window.location.href = response.checkout_url
  } catch (err: any) {
    showError("Erro", err.message || "Erro ao trocar plano")
  } finally {
    isChangingPlano.value = false
  }
}

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

function getDuracaoLabel(meses: number): string {
  switch (meses) {
    case 3:
      return "Trimestral"
    case 6:
      return "Semestral"
    case 12:
      return "Anual"
    default:
      return `${meses} meses`
  }
}

function openChangePlanoDialog() {
  selectedPlano.value = null
  showChangePlanoDialog.value = true
}

// Majoracao - Computed
const majoracaoAlterada = computed(() => {
  return majoracaoDiaria.value !== majoracaoOriginal.value
})

const majoracaoValida = computed(() => {
  const valor = parseFloat(majoracaoDiaria.value)
  return !isNaN(valor) && valor >= 0
})

// Majoracao - Funcoes
async function loadMajoracao() {
  try {
    isLoadingMajoracao.value = true
    majoracaoError.value = null
    const response = await api.get<{ data: { majoracao_diaria: string } }>("/perfil/majoracao")
    majoracaoDiaria.value = response.data.majoracao_diaria
    majoracaoOriginal.value = response.data.majoracao_diaria
  } catch {
    majoracaoError.value = "Erro ao carregar majoracao"
  } finally {
    isLoadingMajoracao.value = false
  }
}

async function saveMajoracao() {
  if (!majoracaoValida.value) {
    majoracaoError.value = "Valor invalido. Use um numero maior ou igual a zero."
    return
  }

  try {
    isSavingMajoracao.value = true
    majoracaoError.value = null
    const response = await api.put<{ data: { majoracao_diaria: string } }>("/perfil/majoracao", {
      majoracao_diaria: parseFloat(majoracaoDiaria.value),
    })
    majoracaoDiaria.value = response.data.majoracao_diaria
    majoracaoOriginal.value = response.data.majoracao_diaria
    success("Majoracao atualizada", "O valor da majoracao foi salvo com sucesso.")
  } catch (err: any) {
    majoracaoError.value = err.message || "Erro ao salvar majoracao"
  } finally {
    isSavingMajoracao.value = false
  }
}

function resetMajoracao() {
  majoracaoDiaria.value = majoracaoOriginal.value
  majoracaoError.value = null
}

onMounted(async () => {
  const urlParams = new URLSearchParams(window.location.search)
  if (urlParams.has("refresh")) {
    window.history.replaceState({}, "", window.location.pathname)
  }
  if (urlParams.get("assinatura") === "sucesso") {
    success("Assinatura ativada!", "Obrigado por assinar o Grapo.")
    window.history.replaceState({}, "", window.location.pathname)
  }
  await Promise.all([loadConnectStatus(), loadAssinaturaStatus(), loadPlanos(), loadMajoracao()])
})
</script>

<template>
  <div class="space-y-6">
    <!-- Cabecalho -->
    <div>
      <h1 class="text-2xl font-bold">Meu Perfil</h1>
      <p class="text-muted-foreground">
        Gerencie suas informacoes e configuracoes
      </p>
    </div>

    <!-- Informacoes do Usuario -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <User class="size-5" />
          Informacoes Pessoais
        </CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="flex items-center gap-4">
          <div class="size-16 rounded-full bg-primary/10 flex items-center justify-center">
            <User class="size-8 text-primary" />
          </div>
          <div>
            <h3 class="text-lg font-semibold">{{ user?.name }}</h3>
            <div class="flex items-center gap-2 text-muted-foreground">
              <Mail class="size-4" />
              {{ user?.email }}
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Majoracao da Diaria -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Percent class="size-5" />
          Majoracao da Diaria
        </CardTitle>
        <CardDescription>
          Percentual aplicado sobre o valor mensal para calcular a diaria sugerida
        </CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <!-- Loading -->
        <div v-if="isLoadingMajoracao" class="flex items-center justify-center py-4">
          <Spinner />
        </div>

        <template v-else>
          <!-- Erro -->
          <div
            v-if="majoracaoError"
            class="bg-destructive/10 text-destructive rounded-lg p-4 flex items-center gap-3"
          >
            <AlertTriangle class="size-5" />
            {{ majoracaoError }}
          </div>

          <!-- Explicacao -->
          <div class="bg-muted/50 rounded-lg p-4 text-sm text-muted-foreground">
            <p>
              A majoracao e um percentual adicional aplicado sobre o valor mensal do ativo
              para calcular a diaria sugerida.
            </p>
            <p class="mt-2">
              <strong>Formula:</strong> Diaria = (Valor Mensal × (1 + Majoracao%)) ÷ 30
            </p>
          </div>

          <!-- Input -->
          <div class="flex items-end gap-4">
            <div class="flex-1 space-y-2">
              <label class="text-sm font-medium" for="majoracao">Percentual de majoracao (%)</label>
              <div class="relative">
                <input
                  id="majoracao"
                  v-model="majoracaoDiaria"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full h-10 px-3 pr-10 rounded-md border border-input bg-background text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                  placeholder="10.00"
                />
                <Percent class="absolute right-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
              </div>
              <p class="text-xs text-muted-foreground">
                Valor padrao: 10%. Use 0 para nao aplicar majoracao.
              </p>
            </div>
          </div>

          <!-- Acoes -->
          <div class="flex gap-3">
            <Button
              :disabled="!majoracaoAlterada || !majoracaoValida || isSavingMajoracao"
              @click="saveMajoracao"
              class="gap-2"
            >
              <Spinner v-if="isSavingMajoracao" size="sm" />
              <Save v-else class="size-4" />
              Salvar
            </Button>
            <Button
              v-if="majoracaoAlterada"
              variant="outline"
              @click="resetMajoracao"
            >
              Cancelar
            </Button>
          </div>
        </template>
      </CardContent>
    </Card>

    <!-- Assinatura -->
    <Card>
      <CardHeader>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div
              class="size-12 rounded-full flex items-center justify-center"
              :class="{
                'bg-green-100': assinaturaAtiva,
                'bg-amber-100': assinaturaCancelada,
                'bg-muted': !assinaturaStatus?.has_subscription,
              }"
            >
              <Crown
                class="size-6"
                :class="{
                  'text-green-600': assinaturaAtiva,
                  'text-amber-600': assinaturaCancelada,
                  'text-muted-foreground': !assinaturaStatus?.has_subscription,
                }"
              />
            </div>
            <div>
              <CardTitle>Assinatura</CardTitle>
              <CardDescription>
                <template v-if="assinaturaAtiva">Sua assinatura esta ativa</template>
                <template v-else-if="assinaturaCancelada">Assinatura cancelada</template>
                <template v-else>Assine para ter acesso completo</template>
              </CardDescription>
            </div>
          </div>
          <Badge
            :variant="assinaturaAtiva ? 'default' : assinaturaCancelada ? 'warning' : 'secondary'"
          >
            <template v-if="assinaturaAtiva">Ativa</template>
            <template v-else-if="assinaturaCancelada">Cancelada</template>
            <template v-else>Trial</template>
          </Badge>
        </div>
      </CardHeader>
      <CardContent class="space-y-6">
        <!-- Loading -->
        <div v-if="isLoadingAssinatura" class="flex items-center justify-center py-4">
          <Spinner />
        </div>

        <template v-else>
          <!-- Info da assinatura -->
          <div class="grid gap-4 sm:grid-cols-2">
            <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/50">
              <Calendar class="size-5 text-muted-foreground" />
              <div>
                <div class="text-sm font-medium">Acesso ate</div>
                <div class="text-sm text-muted-foreground">
                  <template v-if="assinaturaStatus?.data_limite_acesso">
                    {{ formatDate(assinaturaStatus.data_limite_acesso) }}
                    <span v-if="diasRestantes !== null && diasRestantes <= 7" class="text-amber-600">
                      ({{ diasRestantes }} dias)
                    </span>
                  </template>
                  <template v-else>-</template>
                </div>
              </div>
            </div>
            <div
              v-if="assinaturaStatus?.subscription"
              class="flex items-center gap-3 p-3 rounded-lg bg-muted/50"
            >
              <CreditCard class="size-5 text-muted-foreground" />
              <div>
                <div class="text-sm font-medium">Plano atual</div>
                <div class="text-sm text-muted-foreground">
                  {{ assinaturaStatus.subscription.plan.name }}
                </div>
              </div>
            </div>
          </div>

          <!-- Alerta de cancelamento -->
          <div
            v-if="assinaturaCancelada"
            class="bg-amber-50 border border-amber-200 rounded-lg p-4"
          >
            <div class="flex items-start gap-3">
              <AlertTriangle class="size-5 text-amber-600 mt-0.5" />
              <div>
                <p class="font-medium text-amber-800">Assinatura cancelada</p>
                <p class="text-sm text-amber-700">
                  Voce pode continuar usando ate
                  {{ formatDate(assinaturaStatus?.data_limite_acesso || "") }}.
                  Para continuar usando apos essa data, assine novamente.
                </p>
              </div>
            </div>
          </div>

          <!-- Alerta de trial expirando -->
          <div
            v-if="!assinaturaStatus?.has_subscription && diasRestantes !== null && diasRestantes <= 3"
            class="bg-amber-50 border border-amber-200 rounded-lg p-4"
          >
            <div class="flex items-start gap-3">
              <AlertTriangle class="size-5 text-amber-600 mt-0.5" />
              <div>
                <p class="font-medium text-amber-800">Seu trial esta acabando</p>
                <p class="text-sm text-amber-700">
                  Restam apenas {{ diasRestantes }} dia(s). Assine agora para continuar usando.
                </p>
              </div>
            </div>
          </div>

          <!-- Acoes -->
          <div class="flex flex-wrap gap-3">
            <Button
              v-if="!assinaturaStatus?.has_subscription || assinaturaCancelada"
              @click="openChangePlanoDialog"
              class="gap-2"
            >
              <Crown class="size-4" />
              {{ assinaturaCancelada ? "Renovar Assinatura" : "Assinar Agora" }}
            </Button>
            <Button
              v-if="assinaturaAtiva"
              @click="openChangePlanoDialog"
              variant="outline"
              class="gap-2"
            >
              <RefreshCw class="size-4" />
              Trocar Plano
            </Button>
            <Button
              v-if="assinaturaAtiva"
              @click="showCancelDialog = true"
              variant="ghost"
              class="text-destructive hover:text-destructive gap-2"
            >
              <XCircle class="size-4" />
              Cancelar
            </Button>
          </div>
        </template>
      </CardContent>
    </Card>

    <!-- Stripe Connect -->
    <Card>
      <CardHeader>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div
              class="size-12 rounded-full flex items-center justify-center"
              :class="{
                'bg-muted': accountStatus === 'not_configured',
                'bg-amber-100': accountStatus === 'incomplete' || accountStatus === 'pending',
                'bg-green-100': accountStatus === 'active',
              }"
            >
              <CreditCard
                class="size-6"
                :class="{
                  'text-muted-foreground': accountStatus === 'not_configured',
                  'text-amber-600': accountStatus === 'incomplete' || accountStatus === 'pending',
                  'text-green-600': accountStatus === 'active',
                }"
              />
            </div>
            <div>
              <CardTitle>Stripe Connect</CardTitle>
              <CardDescription>
                <template v-if="accountStatus === 'not_configured'">
                  Configure para receber pagamentos dos locatarios
                </template>
                <template v-else-if="accountStatus === 'incomplete'">
                  Complete a configuracao da sua conta
                </template>
                <template v-else-if="accountStatus === 'pending'">
                  Aguardando aprovacao do Stripe
                </template>
                <template v-else>
                  Conta ativa e pronta para receber pagamentos
                </template>
              </CardDescription>
            </div>
          </div>
          <Badge
            :variant="accountStatus === 'active' ? 'default' : 'secondary'"
            class="text-sm"
          >
            <template v-if="accountStatus === 'not_configured'">Nao configurado</template>
            <template v-else-if="accountStatus === 'incomplete'">Incompleto</template>
            <template v-else-if="accountStatus === 'pending'">Pendente</template>
            <template v-else>Ativo</template>
          </Badge>
        </div>
      </CardHeader>
      <CardContent class="space-y-6">
        <!-- Loading -->
        <div v-if="isLoadingConnect" class="flex items-center justify-center py-4">
          <Spinner />
        </div>

        <!-- Erro -->
        <div
          v-else-if="error"
          class="bg-destructive/10 text-destructive rounded-lg p-4 flex items-center gap-3"
        >
          <AlertTriangle class="size-5" />
          {{ error }}
        </div>

        <template v-else>
          <!-- Alerta de pendencias -->
          <div
            v-if="connectStatus?.has_account && hasPendingRequirements"
            class="bg-amber-50 border border-amber-200 rounded-lg p-4 space-y-3"
          >
            <div class="flex items-start gap-3">
              <AlertTriangle class="size-5 text-amber-600 mt-0.5 shrink-0" />
              <div class="flex-1">
                <p class="font-medium text-amber-800">
                  {{ getDisabledReasonText(connectStatus.disabled_reason) }}
                </p>
                <p class="text-sm text-amber-700 mt-1">
                  Sua conta precisa de informacoes adicionais para receber pagamentos.
                </p>
                <ul v-if="connectStatus.requirements_errors?.length" class="mt-2 space-y-1">
                  <li
                    v-for="err in connectStatus.requirements_errors"
                    :key="err.code"
                    class="text-sm text-amber-700"
                  >
                    {{ err.reason }}
                  </li>
                </ul>
              </div>
            </div>
            <a
              v-if="connectStatus.onboarding_url"
              :href="connectStatus.onboarding_url"
              class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors text-sm font-medium"
            >
              <ExternalLink class="size-4" />
              Resolver Pendencias no Stripe
            </a>
          </div>

          <!-- Status detalhado -->
          <div v-if="connectStatus?.has_account" class="grid gap-4 sm:grid-cols-3">
            <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/50">
              <component
                :is="connectStatus.onboarding_complete ? CheckCircle : XCircle"
                class="size-5"
                :class="connectStatus.onboarding_complete ? 'text-green-600' : 'text-muted-foreground'"
              />
              <div>
                <div class="text-sm font-medium">Cadastro</div>
                <div class="text-xs text-muted-foreground">
                  {{ connectStatus.onboarding_complete ? "Completo" : "Incompleto" }}
                </div>
              </div>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/50">
              <component
                :is="connectStatus.charges_enabled ? CheckCircle : XCircle"
                class="size-5"
                :class="connectStatus.charges_enabled ? 'text-green-600' : 'text-muted-foreground'"
              />
              <div>
                <div class="text-sm font-medium">Receber Pagamentos</div>
                <div class="text-xs text-muted-foreground">
                  {{ connectStatus.charges_enabled ? "Habilitado" : "Desabilitado" }}
                </div>
              </div>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-lg bg-muted/50">
              <component
                :is="connectStatus.payouts_enabled ? CheckCircle : XCircle"
                class="size-5"
                :class="connectStatus.payouts_enabled ? 'text-green-600' : 'text-muted-foreground'"
              />
              <div>
                <div class="text-sm font-medium">Transferencias</div>
                <div class="text-xs text-muted-foreground">
                  {{ connectStatus.payouts_enabled ? "Habilitado" : "Desabilitado" }}
                </div>
              </div>
            </div>
          </div>

          <!-- Acoes -->
          <div class="flex flex-wrap gap-3">
            <!-- Sem conta - Iniciar configuracao -->
            <template v-if="accountStatus === 'not_configured'">
              <Button
                @click="startOnboarding"
                :disabled="isProcessing"
                class="gap-2"
              >
                <CreditCard class="size-4" />
                Configurar Stripe Connect
              </Button>
            </template>

            <!-- Conta incompleta - Continuar -->
            <template v-else-if="accountStatus === 'incomplete'">
              <Button
                @click="continueOnboarding"
                :disabled="isProcessing"
                class="gap-2"
              >
                <RefreshCw class="size-4" />
                Continuar Configuracao
              </Button>
            </template>

            <!-- Conta ativa - Dashboard -->
            <template v-else-if="accountStatus === 'active'">
              <Button
                @click="openDashboard"
                :disabled="isProcessing"
                variant="outline"
                class="gap-2"
              >
                <ExternalLink class="size-4" />
                Abrir Dashboard Stripe
              </Button>
            </template>

            <!-- Atualizar status -->
            <Button
              v-if="connectStatus?.has_account"
              @click="refreshConnectStatus"
              :disabled="isLoadingConnect"
              variant="ghost"
              class="gap-2"
            >
              <RefreshCw class="size-4" :class="{ 'animate-spin': isLoadingConnect }" />
              Atualizar Status
            </Button>
          </div>

          <!-- Info -->
          <div v-if="!connectStatus?.has_account" class="border-t pt-6">
            <h4 class="font-medium mb-3">Como funciona?</h4>
            <div class="grid gap-4 sm:grid-cols-3">
              <div class="space-y-2">
                <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                  1
                </div>
                <p class="text-sm text-muted-foreground">
                  Configure sua conta Stripe com seus dados bancarios
                </p>
              </div>
              <div class="space-y-2">
                <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                  2
                </div>
                <p class="text-sm text-muted-foreground">
                  Ative o pagamento nos contratos definindo o dia de vencimento
                </p>
              </div>
              <div class="space-y-2">
                <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                  3
                </div>
                <p class="text-sm text-muted-foreground">
                  Receba 100% dos pagamentos automaticamente na sua conta
                </p>
              </div>
            </div>
          </div>
        </template>
      </CardContent>
    </Card>

    <!-- Dialog Cancelar Assinatura -->
    <Dialog
      v-model:open="showCancelDialog"
      title="Cancelar Assinatura"
      description="Tem certeza que deseja cancelar?"
    >
      <div class="space-y-4">
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
          <div class="flex items-start gap-3">
            <AlertTriangle class="size-5 text-amber-600 mt-0.5" />
            <div class="text-sm">
              <p class="font-medium text-amber-800">Atencao</p>
              <p class="text-amber-700">
                Ao cancelar, voce podera usar o sistema ate 30 dias apos o ultimo pagamento.
                Depois disso, nao tera acesso as funcionalidades de contrato.
              </p>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="showCancelDialog = false">Voltar</Button>
          <Button
            variant="destructive"
            :disabled="isCancelingAssinatura"
            @click="cancelarAssinatura"
          >
            <Spinner v-if="isCancelingAssinatura" size="sm" class="mr-2" />
            Cancelar Assinatura
          </Button>
        </DialogFooter>
      </div>
    </Dialog>

    <!-- Dialog Trocar/Assinar Plano -->
    <Dialog
      v-model:open="showChangePlanoDialog"
      title="Escolher Plano"
      description="Selecione o plano desejado"
    >
      <div class="space-y-4">
        <div v-if="isLoadingPlanos" class="flex justify-center py-4">
          <Spinner />
        </div>

        <div v-else class="grid gap-3">
          <button
            v-for="plano in planos"
            :key="plano.id"
            type="button"
            class="relative flex items-center justify-between p-4 rounded-lg border-2 transition-colors text-left"
            :class="
              selectedPlano === plano.id
                ? 'border-primary bg-primary/5'
                : 'border-border hover:border-muted-foreground/50'
            "
            @click="selectedPlano = plano.id"
          >
            <div>
              <p class="font-medium">{{ getDuracaoLabel(plano.duracao_meses) }}</p>
              <p class="text-sm text-muted-foreground">
                {{ formatCurrency(parseFloat(plano.valor) / plano.duracao_meses) }}/mes
              </p>
            </div>
            <div class="text-right">
              <p class="font-semibold">{{ formatCurrency(plano.valor) }}</p>
              <p class="text-xs text-muted-foreground">total</p>
            </div>
            <div
              v-if="selectedPlano === plano.id"
              class="absolute -top-2 -right-2 size-6 rounded-full bg-primary flex items-center justify-center"
            >
              <Check class="size-4 text-primary-foreground" />
            </div>
          </button>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="showChangePlanoDialog = false">Cancelar</Button>
          <Button :disabled="!selectedPlano || isChangingPlano" @click="trocarPlano">
            <Spinner v-if="isChangingPlano" size="sm" class="mr-2" />
            Continuar para Pagamento
          </Button>
        </DialogFooter>
      </div>
    </Dialog>
  </div>
</template>
