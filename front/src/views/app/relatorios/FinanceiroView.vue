<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Badge } from "@/components/ui/badge"
import { Spinner } from "@/components/ui/spinner"
import { StatCard } from "@/components/app"
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from "@/components/ui/table"
import {
  DollarSign,
  TrendingUp,
  TrendingDown,
  AlertTriangle,
  Calendar,
  Filter,
  Users,
  Package,
  Percent,
} from "lucide-vue-next"
import api from "@/lib/api"

interface Resumo {
  total_faturado: number
  total_recebido: number
  total_pendente: number
  total_atrasado: number
  taxa_inadimplencia: number
}

interface FaturamentoMensal {
  mes: string
  valor: number
  quantidade: number
}

interface FaturamentoPorAtivo {
  tipo_ativo_id: string | null
  tipo_ativo: string
  valor: number
  quantidade: number
}

interface PagamentoInadimplente {
  id: string
  contrato_codigo: string
  locatario: string
  valor: number
  data_vencimento: string
  dias_atraso: number
}

interface Inadimplencia {
  quantidade: number
  valor_total: number
  pagamentos: PagamentoInadimplente[]
}

interface AnaliticoLocatario {
  locatario_id: string
  locatario: string
  total_pago: number
  qtd_pagamentos: number
  total_atrasado: number
  qtd_atrasados: number
}

interface RelatorioData {
  periodo: { inicio: string; fim: string }
  resumo: Resumo
  faturamento_mensal: FaturamentoMensal[]
  faturamento_por_ativo: FaturamentoPorAtivo[]
  inadimplencia: Inadimplencia
  analitico_por_locatario: AnaliticoLocatario[]
}

const data = ref<RelatorioData | null>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)

// Filtros
const dataInicio = ref("")
const dataFim = ref("")

// Inicializa filtros com ultimos 12 meses
onMounted(() => {
  const hoje = new Date()
  const inicio = new Date(hoje.getFullYear(), hoje.getMonth() - 11, 1)

  dataFim.value = hoje.toISOString().split("T")[0] ?? ""
  dataInicio.value = inicio.toISOString().split("T")[0] ?? ""

  loadData()
})

async function loadData(): Promise<void> {
  isLoading.value = true
  error.value = null

  try {
    const params: Record<string, string> = {}
    if (dataInicio.value) params.data_inicio = dataInicio.value
    if (dataFim.value) params.data_fim = dataFim.value

    const response = await api.get<RelatorioData>("/relatorios/financeiro", params)
    data.value = response
  } catch (err) {
    error.value = "Erro ao carregar relatorio financeiro"
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

function handleFilter(): void {
  loadData()
}

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

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString("pt-BR")
}

// Calculo de altura das barras do grafico
const maxFaturamentoMensal = computed(() => {
  if (!data.value?.faturamento_mensal.length) return 1
  return Math.max(...data.value.faturamento_mensal.map((f) => f.valor), 1)
})

function getBarHeight(valor: number): string {
  const percentual = (valor / maxFaturamentoMensal.value) * 100
  return `${Math.max(percentual, 2)}%`
}

// Cor da barra de ativo
function getAtivoBarColor(index: number): string {
  const colors = ["bg-blue-500", "bg-green-500", "bg-amber-500", "bg-purple-500", "bg-pink-500", "bg-cyan-500"]
  return colors[index % colors.length] ?? "bg-gray-500"
}

// Percentual do ativo em relacao ao total
const totalFaturamentoPorAtivo = computed(() => {
  if (!data.value?.faturamento_por_ativo.length) return 0
  return data.value.faturamento_por_ativo.reduce((sum, a) => sum + a.valor, 0)
})

function getAtivoPercentual(valor: number): number {
  if (totalFaturamentoPorAtivo.value <= 0) return 0
  return Math.round((valor / totalFaturamentoPorAtivo.value) * 100)
}
</script>

<template>
  <div class="space-y-6">
    <!-- Cabecalho -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Relatorio Financeiro</h1>
        <p class="text-muted-foreground">
          Analise de faturamento, inadimplencia e receitas por locatario
        </p>
      </div>
    </div>

    <!-- Filtros -->
    <Card>
      <CardContent class="p-4">
        <div class="flex flex-wrap items-end gap-4">
          <div class="flex items-center gap-2">
            <Filter class="size-5 text-muted-foreground" />
            <span class="font-medium">Filtros</span>
          </div>
          <div class="flex-1 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="space-y-2">
              <Label for="data_inicio">Data Inicio</Label>
              <Input
                id="data_inicio"
                v-model="dataInicio"
                type="date"
              />
            </div>
            <div class="space-y-2">
              <Label for="data_fim">Data Fim</Label>
              <Input
                id="data_fim"
                v-model="dataFim"
                type="date"
              />
            </div>
          </div>
          <Button @click="handleFilter" :disabled="isLoading">
            <Calendar class="size-4 mr-2" />
            Filtrar
          </Button>
        </div>
      </CardContent>
    </Card>

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
      <!-- Cards de Resumo - Faturamento -->
      <div class="grid gap-4 md:grid-cols-2">
        <StatCard
          title="Total Faturado"
          :value="formatCurrency(data.resumo.total_faturado)"
          description="Periodo selecionado"
          :icon="DollarSign"
        />
        <StatCard
          title="Total Recebido"
          :value="formatCurrency(data.resumo.total_recebido)"
          description="Pagamentos confirmados"
          :icon="TrendingUp"
        />
      </div>

      <!-- Cards de Resumo - Pendencias e Inadimplencia -->
      <div class="grid gap-4 md:grid-cols-3">
        <StatCard
          title="Total Pendente"
          :value="formatCurrency(data.resumo.total_pendente)"
          description="Aguardando pagamento"
          :icon="Calendar"
        />
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Total Atrasado</p>
                <p class="text-2xl font-bold text-destructive">
                  {{ formatCurrency(data.resumo.total_atrasado) }}
                </p>
                <p class="text-xs text-muted-foreground">Inadimplencia</p>
              </div>
              <div class="flex size-12 items-center justify-center rounded-full bg-destructive/10">
                <TrendingDown class="size-6 text-destructive" />
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground">Taxa Inadimplencia</p>
                <p
                  class="text-2xl font-bold"
                  :class="data.resumo.taxa_inadimplencia > 10 ? 'text-destructive' : data.resumo.taxa_inadimplencia > 5 ? 'text-amber-600' : 'text-green-600'"
                >
                  {{ data.resumo.taxa_inadimplencia.toFixed(1) }}%
                </p>
                <p class="text-xs text-muted-foreground">Atrasado / Faturado</p>
              </div>
              <div
                class="flex size-12 items-center justify-center rounded-full"
                :class="data.resumo.taxa_inadimplencia > 10 ? 'bg-destructive/10' : data.resumo.taxa_inadimplencia > 5 ? 'bg-amber-100' : 'bg-green-600/10'"
              >
                <Percent
                  class="size-6"
                  :class="data.resumo.taxa_inadimplencia > 10 ? 'text-destructive' : data.resumo.taxa_inadimplencia > 5 ? 'text-amber-600' : 'text-green-600'"
                />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Graficos lado a lado -->
      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Grafico de Faturamento Mensal -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <TrendingUp class="size-5" />
              Faturamento Mensal
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="data.faturamento_mensal.length === 0" class="text-center py-8 text-muted-foreground">
              Nenhum faturamento no periodo.
            </div>
            <div v-else class="space-y-4">
              <!-- Grafico de barras verticais -->
              <div class="h-48 flex items-end gap-1 border-b border-l pl-2 pb-2 relative">
                <div
                  v-for="fat in data.faturamento_mensal"
                  :key="fat.mes"
                  class="flex-1 flex flex-col items-center justify-end gap-1 relative z-10"
                >
                  <div class="text-xs font-medium text-center whitespace-nowrap mb-1 max-w-full truncate">
                    {{ formatCurrency(fat.valor) }}
                  </div>
                  <div
                    class="w-full max-w-10 bg-primary rounded-t transition-all duration-300 hover:bg-primary/80"
                    :style="{ height: getBarHeight(fat.valor) }"
                    :title="`${formatMonth(fat.mes)}: ${formatCurrency(fat.valor)}`"
                  ></div>
                </div>
              </div>
              <!-- Labels dos meses -->
              <div class="flex gap-1 ml-2">
                <div
                  v-for="fat in data.faturamento_mensal"
                  :key="fat.mes"
                  class="flex-1 text-center text-xs text-muted-foreground"
                >
                  {{ formatMonth(fat.mes) }}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Grafico de Faturamento por Ativo -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Package class="size-5" />
              Faturamento por Ativo
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="data.faturamento_por_ativo.length === 0" class="text-center py-8 text-muted-foreground">
              Nenhum ativo no periodo.
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="(ativo, index) in data.faturamento_por_ativo"
                :key="ativo.tipo_ativo_id || index"
                class="space-y-1"
              >
                <div class="flex items-center justify-between text-sm">
                  <span class="font-medium">{{ ativo.tipo_ativo }}</span>
                  <span class="text-muted-foreground">
                    {{ formatCurrency(ativo.valor) }} ({{ getAtivoPercentual(ativo.valor) }}%)
                  </span>
                </div>
                <div class="h-4 bg-muted rounded-full overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all duration-300"
                    :class="getAtivoBarColor(index)"
                    :style="{ width: `${getAtivoPercentual(ativo.valor)}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Inadimplencia -->
      <Card v-if="data.inadimplencia.quantidade > 0">
        <CardHeader>
          <CardTitle class="flex items-center gap-2 text-destructive">
            <AlertTriangle class="size-5" />
            Inadimplencia
            <Badge variant="destructive">{{ data.inadimplencia.quantidade }} pagamentos</Badge>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Contrato</TableHead>
                  <TableHead>Locatario</TableHead>
                  <TableHead>Valor</TableHead>
                  <TableHead>Vencimento</TableHead>
                  <TableHead>Dias Atraso</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="pag in data.inadimplencia.pagamentos" :key="pag.id">
                  <TableCell class="font-medium">{{ pag.contrato_codigo }}</TableCell>
                  <TableCell>{{ pag.locatario }}</TableCell>
                  <TableCell>{{ formatCurrency(pag.valor) }}</TableCell>
                  <TableCell>{{ formatDate(pag.data_vencimento) }}</TableCell>
                  <TableCell>
                    <Badge
                      :variant="pag.dias_atraso > 30 ? 'destructive' : pag.dias_atraso > 15 ? 'secondary' : 'outline'"
                    >
                      {{ pag.dias_atraso }} dias
                    </Badge>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <!-- Analitico por Locatario -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Users class="size-5" />
            Analitico por Locatario
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="data.analitico_por_locatario.length === 0" class="text-center py-8 text-muted-foreground">
            Nenhum pagamento no periodo.
          </div>
          <div v-else class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Locatario</TableHead>
                  <TableHead class="text-right">Total Pago</TableHead>
                  <TableHead class="text-center">Qtd. Pagamentos</TableHead>
                  <TableHead class="text-right">Total Atrasado</TableHead>
                  <TableHead class="text-center">Qtd. Atrasados</TableHead>
                  <TableHead class="text-center">Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="loc in data.analitico_por_locatario" :key="loc.locatario_id">
                  <TableCell class="font-medium">{{ loc.locatario }}</TableCell>
                  <TableCell class="text-right">{{ formatCurrency(loc.total_pago) }}</TableCell>
                  <TableCell class="text-center">{{ loc.qtd_pagamentos }}</TableCell>
                  <TableCell class="text-right">
                    <span :class="loc.total_atrasado > 0 ? 'text-destructive font-medium' : ''">
                      {{ formatCurrency(loc.total_atrasado) }}
                    </span>
                  </TableCell>
                  <TableCell class="text-center">
                    <span :class="loc.qtd_atrasados > 0 ? 'text-destructive font-medium' : ''">
                      {{ loc.qtd_atrasados }}
                    </span>
                  </TableCell>
                  <TableCell class="text-center">
                    <Badge
                      :variant="loc.qtd_atrasados > 0 ? 'destructive' : 'success'"
                    >
                      {{ loc.qtd_atrasados > 0 ? 'Inadimplente' : 'Regular' }}
                    </Badge>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
    </template>
  </div>
</template>
