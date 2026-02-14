<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import { useRouter, useRoute, RouterLink } from "vue-router"
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
  Mail,
  Phone,
  MapPin,
  Pencil,
  Eye,
} from "lucide-vue-next"
import api from "@/lib/api"
import type { Pessoa, Contrato, ContratoStatus, ApiResponse } from "@/types"

const router = useRouter()
const route = useRoute()
const { error } = useNotification()

const isLoading = ref(true)
const locatario = ref<Pessoa | null>(null)

const contratos = computed(() => locatario.value?.contratos_como_locatario || [])

onMounted(async () => {
  await loadLocatario()
})

async function loadLocatario(): Promise<void> {
  try {
    const response = await api.get<ApiResponse<Pessoa>>(
      `/locatarios/${route.params.id}`,
    )
    locatario.value = response.data
  } catch {
    error("Erro", "Locatario nao encontrado")
    router.push({ name: "locatarios.index" })
  } finally {
    isLoading.value = false
  }
}

function formatPhone(phone: string | null): string {
  if (!phone) return "-"
  const digits = phone.replace(/\D/g, "")
  if (digits.length === 11) {
    return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`
  }
  if (digits.length === 10) {
    return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`
  }
  return phone
}

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

function formatDocumento(tipo: string, numero: string): string {
  if (tipo === "cpf") {
    const digits = numero.replace(/\D/g, "")
    if (digits.length === 11) {
      return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6, 9)}-${digits.slice(9)}`
    }
  }
  if (tipo === "cnpj") {
    const digits = numero.replace(/\D/g, "")
    if (digits.length === 14) {
      return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5, 8)}/${digits.slice(8, 12)}-${digits.slice(12)}`
    }
  }
  return numero
}

function getTipoDocumentoLabel(tipo: string): string {
  const labels: Record<string, string> = {
    cpf: "CPF",
    cnpj: "CNPJ",
    rg: "RG",
    cnh: "CNH",
    outro: "Outro",
  }
  return labels[tipo] || tipo
}

// Resumo dos contratos
const resumoContratos = computed(() => {
  const lista = contratos.value
  const ativos = lista.filter((c: Contrato) => c.status === "ativo")
  const valorTotalAtivos = ativos.reduce((sum: number, c: Contrato) => sum + Number(c.valor_total || 0), 0)

  return {
    total: lista.length,
    ativos: ativos.length,
    valorTotalAtivos,
  }
})
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
              {{ locatario?.nome || "..." }}
            </h1>
            <Badge
              v-if="locatario"
              :variant="locatario.ativo ? 'success' : 'secondary'"
            >
              {{ locatario.ativo ? "Ativo" : "Inativo" }}
            </Badge>
            <Badge v-if="locatario?.tipo_pessoa" variant="outline">
              {{ locatario.tipo_pessoa }}
            </Badge>
          </div>
          <p class="text-muted-foreground">Detalhes do locatario</p>
        </div>
      </div>

      <Button v-if="locatario" variant="outline" as-child>
        <RouterLink :to="{ name: 'locatarios.edit', params: { id: locatario.id } }">
          <Pencil class="size-4 mr-2" />
          Editar
        </RouterLink>
      </Button>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <Spinner size="lg" />
    </div>

    <template v-else-if="locatario">
      <!-- Info cards -->
      <div class="grid gap-4 md:grid-cols-2">
        <!-- Dados Pessoais -->
        <Card>
          <CardHeader>
            <CardTitle>Dados Pessoais</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="flex items-center gap-3">
              <Mail class="size-4 text-muted-foreground" />
              <div class="min-w-0 flex-1">
                <p class="text-sm text-muted-foreground">E-mail</p>
                <p class="font-medium truncate">{{ locatario.email || "-" }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <Phone class="size-4 text-muted-foreground" />
              <div>
                <p class="text-sm text-muted-foreground">Telefone</p>
                <p class="font-medium">{{ formatPhone(locatario.telefone) }}</p>
              </div>
            </div>
            <div v-if="locatario.endereco" class="flex items-start gap-3">
              <MapPin class="size-4 text-muted-foreground mt-0.5" />
              <div>
                <p class="text-sm text-muted-foreground">Endereco</p>
                <p class="font-medium">{{ locatario.endereco }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Valor Total Ativos -->
        <Card>
          <CardContent class="p-6 flex flex-col items-center justify-center h-full">
            <p class="text-sm text-muted-foreground mb-1">Valor Total em Contratos Ativos</p>
            <p class="text-3xl font-bold text-primary">{{ formatCurrency(resumoContratos.valorTotalAtivos) }}</p>
            <p class="text-sm text-muted-foreground mt-2">{{ resumoContratos.ativos }} contrato(s) ativo(s)</p>
          </CardContent>
        </Card>
      </div>

      <!-- Documentos -->
      <Card v-if="locatario.documentos && locatario.documentos.length > 0">
        <CardHeader>
          <CardTitle>Documentos</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex flex-wrap gap-3">
            <div
              v-for="doc in locatario.documentos"
              :key="doc.id"
              class="flex items-center gap-2 rounded-lg border px-3 py-2"
            >
              <Badge variant="outline">{{ getTipoDocumentoLabel(doc.tipo) }}</Badge>
              <span class="font-mono text-sm">{{ formatDocumento(doc.tipo, doc.numero) }}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Contratos -->
      <Card>
        <CardHeader class="flex flex-row items-center justify-between">
          <CardTitle>Contratos ({{ resumoContratos.total }})</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Codigo</TableHead>
                  <TableHead>Periodo</TableHead>
                  <TableHead>Valor Total</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead class="w-[80px]">Acoes</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableEmpty
                  v-if="contratos.length === 0"
                  :colspan="5"
                  message="Nenhum contrato encontrado"
                />
                <TableRow v-else v-for="contrato in contratos" :key="contrato.id">
                  <TableCell class="font-medium">{{ contrato.codigo }}</TableCell>
                  <TableCell>
                    {{ formatDate(contrato.data_inicio) }} - {{ formatDate(contrato.data_termino) }}
                  </TableCell>
                  <TableCell>{{ formatCurrency(contrato.valor_total) }}</TableCell>
                  <TableCell>
                    <Badge :variant="getStatusVariant(contrato.status)">
                      {{ getStatusLabel(contrato.status) }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Button variant="ghost" size="icon-sm" as-child>
                      <RouterLink :to="{ name: 'contratos.show', params: { id: contrato.id } }">
                        <Eye class="size-4" />
                      </RouterLink>
                    </Button>
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
