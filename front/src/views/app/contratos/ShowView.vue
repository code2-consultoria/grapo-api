<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import { useRouter, useRoute } from "vue-router"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
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
import { useNotification } from "@/composables"
import {
  ArrowLeft,
  Calendar,
  User,
  FileText,
  Package,
} from "lucide-vue-next"
import api from "@/lib/api"
import type { Contrato, ContratoStatus, ApiResponse } from "@/types"

const router = useRouter()
const route = useRoute()
const { error } = useNotification()

const isLoading = ref(true)
const contrato = ref<Contrato | null>(null)

onMounted(async () => {
  try {
    const response = await api.get<ApiResponse<Contrato>>(
      `/contratos/${route.params.id}`,
    )
    contrato.value = response.data
  } catch (err) {
    error("Erro", "Contrato nao encontrado")
    router.push({ name: "contratos.index" })
  } finally {
    isLoading.value = false
  }
})

function formatCurrency(value: number): string {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value)
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString("pt-BR")
}

function getStatusVariant(status: ContratoStatus): "default" | "success" | "secondary" | "destructive" {
  switch (status) {
    case "rascunho":
      return "secondary"
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

const diasLocacao = computed(() => {
  if (!contrato.value) return 0
  const inicio = new Date(contrato.value.data_inicio)
  const termino = new Date(contrato.value.data_termino)
  const diff = termino.getTime() - inicio.getTime()
  return Math.ceil(diff / (1000 * 60 * 60 * 24)) + 1
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" @click="router.back()">
        <ArrowLeft class="size-5" />
      </Button>
      <div class="flex-1">
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

    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <Spinner size="lg" />
    </div>

    <template v-else-if="contrato">
      <!-- Info cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
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
      </div>

      <!-- Itens do contrato -->
      <Card>
        <CardHeader>
          <CardTitle>Itens do Contrato</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Tipo de Ativo</TableHead>
                  <TableHead>Quantidade</TableHead>
                  <TableHead>Valor Diaria</TableHead>
                  <TableHead>Subtotal</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableEmpty
                  v-if="!contrato.itens || contrato.itens.length === 0"
                  :colspan="4"
                  message="Nenhum item adicionado ao contrato"
                />
                <TableRow v-else v-for="item in contrato.itens" :key="item.id">
                  <TableCell>{{ item.tipo_ativo?.nome || "-" }}</TableCell>
                  <TableCell>{{ item.quantidade }}</TableCell>
                  <TableCell>{{ formatCurrency(item.valor_unitario_diaria) }}</TableCell>
                  <TableCell class="font-medium">
                    {{ formatCurrency(item.valor_total_item) }}
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <!-- Observacoes -->
      <Card v-if="contrato.observacoes">
        <CardHeader>
          <CardTitle>Observacoes</CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-muted-foreground">{{ contrato.observacoes }}</p>
        </CardContent>
      </Card>
    </template>
  </div>
</template>
