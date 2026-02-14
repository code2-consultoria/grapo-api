<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import { RouterLink } from "vue-router"
import { useAuth } from "@/composables"
import { StatCard } from "@/components/app"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Spinner } from "@/components/ui/spinner"
import {
  DollarSign,
  FileText,
  Package,
  Percent,
  Calendar,
  AlertTriangle,
  Info,
  AlertCircle,
  Plus,
  TrendingUp,
  Crown,
  XCircle,
} from "lucide-vue-next"
import api from "@/lib/api"
import type { DashboardData, DashboardAlerta } from "@/types/api"

interface AssinaturaStatus {
  has_subscription: boolean
  data_limite_acesso: string | null
}

const { userName } = useAuth()

const dashboard = ref<DashboardData | null>(null)
const assinaturaStatus = ref<AssinaturaStatus | null>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)

// Assinatura
const acessoExpirado = computed(() => {
  if (!assinaturaStatus.value?.data_limite_acesso) return true
  const dataLimite = new Date(assinaturaStatus.value.data_limite_acesso)
  return dataLimite < new Date()
})

const diasRestantesAssinatura = computed(() => {
  if (!assinaturaStatus.value?.data_limite_acesso) return null
  const dataLimite = new Date(assinaturaStatus.value.data_limite_acesso)
  const hoje = new Date()
  return Math.ceil((dataLimite.getTime() - hoje.getTime()) / (1000 * 60 * 60 * 24))
})

const mostrarAvisoAssinatura = computed(() => {
  if (acessoExpirado.value) return true
  if (diasRestantesAssinatura.value !== null && diasRestantesAssinatura.value <= 7) return true
  return false
})

// Carregar dados
onMounted(async () => {
  try {
    const [dashboardRes, assinaturaRes] = await Promise.all([
      api.get<{ data: DashboardData }>("/dashboard"),
      api.get<{ data: AssinaturaStatus }>("/assinaturas/status"),
    ])
    dashboard.value = dashboardRes.data
    assinaturaStatus.value = assinaturaRes.data
  } catch (err) {
    error.value = "Erro ao carregar dashboard"
    console.error(err)
  } finally {
    isLoading.value = false
  }
})

// Formatacao
function formatCurrency(value: number): string {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value)
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString("pt-BR")
}

// Icone do alerta
function getAlertIcon(tipo: DashboardAlerta["tipo"]) {
  switch (tipo) {
    case "warning":
      return AlertTriangle
    case "info":
      return Info
    case "destructive":
      return AlertCircle
    default:
      return Info
  }
}

// Classe de cor do badge
function getAlertBadgeVariant(
  tipo: DashboardAlerta["tipo"]
): "default" | "secondary" | "destructive" | "outline" {
  switch (tipo) {
    case "warning":
      return "secondary"
    case "info":
      return "outline"
    case "destructive":
      return "destructive"
    default:
      return "default"
  }
}

// Percentual de ocupacao formatado
const ocupacaoPercent = computed(() => {
  if (!dashboard.value) return 0
  return dashboard.value.operacional.taxa_ocupacao
})
</script>

<template>
  <div class="space-y-6">
    <!-- Cabecalho -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <p class="text-muted-foreground">Bem-vindo de volta, {{ userName }}!</p>
      </div>
      <RouterLink :to="{ name: 'contratos.create' }">
        <Button class="gap-2">
          <Plus class="size-4" />
          Novo Contrato
        </Button>
      </RouterLink>
    </div>

    <!-- Aviso de Assinatura -->
    <Card
      v-if="!isLoading && mostrarAvisoAssinatura"
      class="border-2"
      :class="acessoExpirado ? 'border-destructive bg-destructive/5' : 'border-amber-500 bg-amber-50'"
    >
      <CardContent class="p-4">
        <div class="flex items-start gap-4">
          <div
            class="size-12 rounded-full flex items-center justify-center shrink-0"
            :class="acessoExpirado ? 'bg-destructive/10' : 'bg-amber-100'"
          >
            <component
              :is="acessoExpirado ? XCircle : Crown"
              class="size-6"
              :class="acessoExpirado ? 'text-destructive' : 'text-amber-600'"
            />
          </div>
          <div class="flex-1">
            <h3 class="font-semibold" :class="acessoExpirado ? 'text-destructive' : 'text-amber-800'">
              <template v-if="acessoExpirado">Acesso bloqueado</template>
              <template v-else>Sua assinatura esta acabando</template>
            </h3>
            <p class="text-sm mt-1" :class="acessoExpirado ? 'text-destructive/80' : 'text-amber-700'">
              <template v-if="acessoExpirado">
                Sua assinatura expirou. Renove para continuar acessando as funcionalidades de contrato.
              </template>
              <template v-else>
                Restam {{ diasRestantesAssinatura }} dia(s) de acesso. Renove para nao perder o acesso.
              </template>
            </p>
          </div>
          <RouterLink :to="{ name: 'perfil' }">
            <Button :variant="acessoExpirado ? 'default' : 'outline'" class="shrink-0 gap-2">
              <Crown class="size-4" />
              Renovar Assinatura
            </Button>
          </RouterLink>
        </div>
      </CardContent>
    </Card>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <Spinner size="lg" />
    </div>

    <!-- Erro -->
    <div
      v-else-if="error"
      class="bg-destructive/10 text-destructive rounded-lg p-4"
    >
      {{ error }}
    </div>

    <!-- Conteudo -->
    <template v-else-if="dashboard">
      <!-- Cards de estatisticas financeiras -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <StatCard
          title="Receita Total"
          :value="formatCurrency(dashboard.financeiro.receita_total)"
          description="Contratos ativos"
          :icon="DollarSign"
        />
        <StatCard
          title="Contratos Ativos"
          :value="dashboard.financeiro.contratos_ativos"
          description="Em andamento"
          :icon="FileText"
        />
        <StatCard
          title="Estoque Total"
          :value="dashboard.operacional.estoque_total"
          description="Unidades cadastradas"
          :icon="Package"
        />
        <StatCard
          title="Taxa de Ocupacao"
          :value="`${ocupacaoPercent}%`"
          :description="`${dashboard.operacional.estoque_alocado} alocados`"
          :icon="Percent"
        />
      </div>

      <!-- Alertas -->
      <div v-if="dashboard.alertas.length > 0" class="space-y-3">
        <h2 class="text-lg font-semibold flex items-center gap-2">
          <AlertTriangle class="size-5 text-amber-500" />
          Alertas
        </h2>
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
          <Card
            v-for="(alerta, index) in dashboard.alertas"
            :key="index"
            class="border-l-4"
            :class="{
              'border-l-amber-500': alerta.tipo === 'warning',
              'border-l-blue-500': alerta.tipo === 'info',
              'border-l-red-500': alerta.tipo === 'destructive',
            }"
          >
            <CardContent class="p-4">
              <div class="flex items-start gap-3">
                <component
                  :is="getAlertIcon(alerta.tipo)"
                  class="size-5 mt-0.5 shrink-0"
                  :class="{
                    'text-amber-500': alerta.tipo === 'warning',
                    'text-blue-500': alerta.tipo === 'info',
                    'text-red-500': alerta.tipo === 'destructive',
                  }"
                />
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="font-medium">{{ alerta.titulo }}</span>
                    <Badge :variant="getAlertBadgeVariant(alerta.tipo)">
                      {{ alerta.tipo === "warning" ? "Atencao" : alerta.tipo === "destructive" ? "Urgente" : "Info" }}
                    </Badge>
                  </div>
                  <p class="text-sm text-muted-foreground">
                    {{ alerta.mensagem }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Secao com dois paineis -->
      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Contratos a vencer -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Calendar class="size-5" />
              Contratos a Vencer (30 dias)
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div
              v-if="dashboard.financeiro.contratos_a_vencer.length === 0"
              class="text-muted-foreground text-sm py-4 text-center"
            >
              Nenhum contrato vencendo nos proximos 30 dias.
            </div>
            <div v-else class="space-y-3">
              <RouterLink
                v-for="contrato in dashboard.financeiro.contratos_a_vencer"
                :key="contrato.id"
                :to="{ name: 'contratos.show', params: { id: contrato.id } }"
                class="flex items-center justify-between p-3 rounded-lg border hover:bg-muted/50 transition-colors"
              >
                <div class="space-y-1">
                  <div class="font-medium">{{ contrato.codigo }}</div>
                  <div class="text-sm text-muted-foreground">
                    {{ contrato.locatario || "Sem locatario" }}
                  </div>
                </div>
                <div class="text-right space-y-1">
                  <Badge
                    :variant="contrato.dias_restantes <= 7 ? 'destructive' : 'secondary'"
                  >
                    {{ contrato.dias_restantes }}
                    {{ contrato.dias_restantes === 1 ? "dia" : "dias" }}
                  </Badge>
                  <div class="text-sm text-muted-foreground">
                    {{ formatDate(contrato.data_termino) }}
                  </div>
                </div>
              </RouterLink>
            </div>
          </CardContent>
        </Card>

        <!-- Top Ativos -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <TrendingUp class="size-5" />
              Top 5 Ativos Mais Alugados
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div
              v-if="dashboard.operacional.top_ativos.length === 0"
              class="text-muted-foreground text-sm py-4 text-center"
            >
              Nenhum ativo em contratos ativos.
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="(ativo, index) in dashboard.operacional.top_ativos"
                :key="ativo.id"
                class="flex items-center gap-4"
              >
                <div
                  class="flex items-center justify-center size-8 rounded-full text-sm font-bold"
                  :class="{
                    'bg-amber-100 text-amber-700': index === 0,
                    'bg-zinc-100 text-zinc-600': index === 1,
                    'bg-orange-100 text-orange-700': index === 2,
                    'bg-muted text-muted-foreground': index > 2,
                  }"
                >
                  {{ index + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-medium truncate">{{ ativo.nome }}</div>
                </div>
                <div class="text-right">
                  <span class="font-semibold">{{ ativo.quantidade }}</span>
                  <span class="text-muted-foreground text-sm ml-1">un.</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Barra de ocupacao -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Package class="size-5" />
            Ocupacao do Estoque
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div class="flex items-center justify-between text-sm">
              <span>Disponivel: {{ dashboard.operacional.estoque_disponivel }}</span>
              <span>Alocado: {{ dashboard.operacional.estoque_alocado }}</span>
            </div>
            <div class="w-full bg-muted rounded-full h-4 overflow-hidden">
              <div
                class="h-full bg-primary transition-all duration-500"
                :style="{ width: `${ocupacaoPercent}%` }"
              />
            </div>
            <div class="flex items-center justify-between text-sm text-muted-foreground">
              <span>Total: {{ dashboard.operacional.estoque_total }} unidades</span>
              <span class="font-medium text-foreground">
                {{ ocupacaoPercent }}% ocupado
              </span>
            </div>

            <!-- Lotes por status -->
            <div class="grid grid-cols-3 gap-4 pt-4 border-t">
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">
                  {{ dashboard.operacional.lotes_por_status.disponivel }}
                </div>
                <div class="text-sm text-muted-foreground">Disponiveis</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-amber-600">
                  {{ dashboard.operacional.lotes_por_status.indisponivel }}
                </div>
                <div class="text-sm text-muted-foreground">Indisponiveis</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-red-600">
                  {{ dashboard.operacional.lotes_por_status.esgotado }}
                </div>
                <div class="text-sm text-muted-foreground">Esgotados</div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </template>
  </div>
</template>
