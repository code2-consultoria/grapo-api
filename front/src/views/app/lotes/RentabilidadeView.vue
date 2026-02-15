<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import { useRoute, RouterLink } from "vue-router"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Spinner } from "@/components/ui/spinner"
import { StatCard } from "@/components/app"
import {
  ArrowLeft,
  DollarSign,
  TrendingUp,
  Percent,
  Package,
  FileText,
  Calendar,
} from "lucide-vue-next"
import api from "@/lib/api"

interface LoteResumo {
  custo_aquisicao: number
  total_recebido: number
  roi_percentual: number
  unidades_alocadas: number
  contratos_count: number
}

interface LoteInfo {
  id: string
  codigo: string
  tipo_ativo: string | null
  quantidade_total: number
  quantidade_disponivel: number
  data_aquisicao: string | null
}

interface PagamentoMes {
  mes: string
  valor: number
  acumulado: number
}

interface OcupacaoMes {
  mes: string
  percentual: number
}

interface RentabilidadeData {
  lote: LoteInfo
  resumo: LoteResumo
  pagamentos_por_mes: PagamentoMes[]
  ocupacao_por_mes: OcupacaoMes[]
}

const route = useRoute()
const loteId = computed(() => route.params.id as string)

const data = ref<RentabilidadeData | null>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)

onMounted(async () => {
  try {
    const response = await api.get<RentabilidadeData>(`/lotes/${loteId.value}/rentabilidade`)
    data.value = response
  } catch (err) {
    error.value = "Erro ao carregar dados de rentabilidade"
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

function formatMonth(mes: string): string {
  const [ano = "", mesNum = "1"] = mes.split("-")
  const meses = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"]
  return `${meses[parseInt(mesNum) - 1]}/${ano.slice(2)}`
}

function formatDate(dateStr: string | null): string {
  if (!dateStr) return "-"
  return new Date(dateStr).toLocaleDateString("pt-BR")
}

// Calculo de altura das barras do grafico de pagamentos (usa valor acumulado)
const maxPagamentoAcumulado = computed(() => {
  if (!data.value?.pagamentos_por_mes.length) return 1
  return Math.max(...data.value.pagamentos_por_mes.map((p) => p.acumulado), 1)
})

function getBarHeight(acumulado: number): string {
  const percentual = (acumulado / maxPagamentoAcumulado.value) * 100
  return `${Math.max(percentual, 2)}%`
}

// Cor do ROI
function getRoiColor(roi: number): string {
  if (roi >= 100) return "text-green-600"
  if (roi >= 50) return "text-blue-600"
  if (roi >= 0) return "text-amber-600"
  return "text-red-600"
}

// Cor da barra de ocupacao
function getOcupacaoColor(percentual: number): string {
  if (percentual >= 80) return "bg-green-500"
  if (percentual >= 50) return "bg-blue-500"
  if (percentual >= 20) return "bg-amber-500"
  return "bg-zinc-300"
}
</script>

<template>
  <div class="space-y-6">
    <!-- Cabecalho -->
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" as-child>
        <RouterLink :to="{ name: 'lotes.index' }">
          <ArrowLeft class="size-5" />
        </RouterLink>
      </Button>
      <div>
        <h1 class="text-2xl font-bold">Rentabilidade do Lote</h1>
        <p class="text-muted-foreground">
          <template v-if="data">{{ data.lote.codigo }} - {{ data.lote.tipo_ativo || "Sem tipo" }}</template>
          <template v-else>Carregando...</template>
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <Spinner size="lg" />
    </div>

    <!-- Erro -->
    <div v-else-if="error" class="bg-destructive/10 text-destructive rounded-lg p-4">
      {{ error }}
    </div>

    <!-- Conteudo -->
    <template v-else-if="data">
      <!-- Informacoes do Lote -->
      <Card>
        <CardContent class="p-4">
          <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center gap-3">
              <Package class="size-5 text-muted-foreground" />
              <div>
                <div class="text-sm text-muted-foreground">Quantidade Total</div>
                <div class="font-semibold">{{ data.lote.quantidade_total }} unidades</div>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <FileText class="size-5 text-muted-foreground" />
              <div>
                <div class="text-sm text-muted-foreground">Disponivel</div>
                <div class="font-semibold">{{ data.lote.quantidade_disponivel }} unidades</div>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <Calendar class="size-5 text-muted-foreground" />
              <div>
                <div class="text-sm text-muted-foreground">Data Aquisicao</div>
                <div class="font-semibold">{{ formatDate(data.lote.data_aquisicao) }}</div>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <FileText class="size-5 text-muted-foreground" />
              <div>
                <div class="text-sm text-muted-foreground">Contratos</div>
                <div class="font-semibold">{{ data.resumo.contratos_count }}</div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Cards de Resumo -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <StatCard
          title="Custo de Aquisicao"
          :value="formatCurrency(data.resumo.custo_aquisicao)"
          description="Valor total + frete"
          :icon="DollarSign"
        />
        <StatCard
          title="Total Recebido"
          :value="formatCurrency(data.resumo.total_recebido)"
          description="Pagamentos confirmados"
          :icon="TrendingUp"
        />
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">ROI</p>
                <p class="text-2xl font-bold" :class="getRoiColor(data.resumo.roi_percentual)">
                  {{ data.resumo.roi_percentual.toFixed(1) }}%
                </p>
                <p class="text-xs text-muted-foreground">Retorno sobre investimento</p>
              </div>
              <div
                class="flex size-12 items-center justify-center rounded-full"
                :class="data.resumo.roi_percentual >= 100 ? 'bg-green-100' : 'bg-muted'"
              >
                <Percent
                  class="size-6"
                  :class="data.resumo.roi_percentual >= 100 ? 'text-green-600' : 'text-muted-foreground'"
                />
              </div>
            </div>
          </CardContent>
        </Card>
        <StatCard
          title="Unidades Alocadas"
          :value="data.resumo.unidades_alocadas"
          :description="`de ${data.lote.quantidade_total} totais`"
          :icon="Package"
        />
      </div>

      <!-- Grafico de Pagamentos -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <TrendingUp class="size-5" />
            Evolucao de Pagamentos (Acumulado)
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="data.pagamentos_por_mes.length === 0" class="text-center py-8 text-muted-foreground">
            Nenhum pagamento registrado para este lote.
          </div>
          <div v-else class="space-y-4">
            <!-- Grafico de barras verticais -->
            <div class="h-64 flex items-end gap-1 border-b border-l pl-2 pb-2 relative">
              <!-- Linhas de grade -->
              <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
                <div class="border-t border-dashed border-muted-foreground/20 w-full"></div>
                <div class="border-t border-dashed border-muted-foreground/20 w-full"></div>
                <div class="border-t border-dashed border-muted-foreground/20 w-full"></div>
                <div class="border-t border-dashed border-muted-foreground/20 w-full"></div>
              </div>
              <!-- Barras (mostra valor acumulado) -->
              <div
                v-for="pag in data.pagamentos_por_mes"
                :key="pag.mes"
                class="flex-1 flex flex-col items-center justify-end gap-1 relative z-10"
              >
                <div class="text-xs font-medium text-center whitespace-nowrap -rotate-0 mb-1">
                  {{ formatCurrency(pag.acumulado) }}
                </div>
                <div
                  class="w-full max-w-12 bg-primary rounded-t transition-all duration-300 hover:bg-primary/80"
                  :style="{ height: getBarHeight(pag.acumulado) }"
                  :title="`${formatMonth(pag.mes)}: ${formatCurrency(pag.valor)} (Acumulado: ${formatCurrency(pag.acumulado)})`"
                ></div>
              </div>
            </div>
            <!-- Labels dos meses -->
            <div class="flex gap-1 ml-2">
              <div
                v-for="pag in data.pagamentos_por_mes"
                :key="pag.mes"
                class="flex-1 text-center text-xs text-muted-foreground"
              >
                {{ formatMonth(pag.mes) }}
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Grafico de Ocupacao -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Percent class="size-5" />
            Taxa de Ocupacao (Ultimos 24 meses)
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <!-- Grafico de barras horizontais -->
            <div class="space-y-2 max-h-96 overflow-y-auto pr-2">
              <div
                v-for="ocup in data.ocupacao_por_mes"
                :key="ocup.mes"
                class="flex items-center gap-3"
              >
                <div class="w-16 text-xs text-muted-foreground shrink-0">
                  {{ formatMonth(ocup.mes) }}
                </div>
                <div class="flex-1 h-6 bg-muted rounded-full overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all duration-300"
                    :class="getOcupacaoColor(ocup.percentual)"
                    :style="{ width: `${ocup.percentual}%` }"
                  ></div>
                </div>
                <div class="w-12 text-right text-sm font-medium">
                  {{ ocup.percentual.toFixed(0) }}%
                </div>
              </div>
            </div>

            <!-- Legenda -->
            <div class="flex items-center gap-6 pt-4 border-t text-sm">
              <div class="flex items-center gap-2">
                <div class="size-3 rounded-full bg-green-500"></div>
                <span class="text-muted-foreground">80%+</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="size-3 rounded-full bg-blue-500"></div>
                <span class="text-muted-foreground">50-79%</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="size-3 rounded-full bg-amber-500"></div>
                <span class="text-muted-foreground">20-49%</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="size-3 rounded-full bg-zinc-300"></div>
                <span class="text-muted-foreground">&lt;20%</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </template>
  </div>
</template>
