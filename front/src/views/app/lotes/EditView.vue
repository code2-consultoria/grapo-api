<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import { useRouter, useRoute } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Select } from "@/components/ui/select"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { FormField, FormGroup, FormActions } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import { useForm, useNotification } from "@/composables"
import { ArrowLeft } from "lucide-vue-next"
import api from "@/lib/api"
import type { Lote, LoteForm, TipoAtivo, ApiResponse, PaginatedResponse } from "@/types"

const router = useRouter()
const route = useRoute()
const { success, error } = useNotification()

const isLoading = ref(true)
const tiposAtivos = ref<{ value: string; label: string }[]>([])

const formasPagamento = [
  { value: "pix", label: "PIX" },
  { value: "boleto", label: "Boleto" },
  { value: "cartao", label: "Cartao" },
  { value: "transferencia", label: "Transferencia" },
  { value: "dinheiro", label: "Dinheiro" },
  { value: "outro", label: "Outro" },
]

const form = useForm<LoteForm>({
  initialValues: {
    codigo: "",
    tipo_ativo_id: "",
    quantidade_total: "",
    fornecedor: "",
    valor_total: "",
    valor_frete: "",
    forma_pagamento: "",
    nf: "",
    data_aquisicao: "",
  },
  validate(values) {
    const errors: Partial<Record<keyof LoteForm, string>> = {}

    if (!values.codigo) errors.codigo = "Codigo e obrigatorio"
    if (!values.tipo_ativo_id) errors.tipo_ativo_id = "Tipo de ativo e obrigatorio"
    if (!values.quantidade_total) {
      errors.quantidade_total = "Quantidade e obrigatoria"
    } else if (Number(values.quantidade_total) <= 0) {
      errors.quantidade_total = "Quantidade deve ser maior que zero"
    }

    return errors
  },
  async onSubmit(values) {
    await api.put(`/lotes/${route.params.id}`, {
      fornecedor: values.fornecedor || null,
      valor_total: values.valor_total ? Number(values.valor_total) : null,
      valor_frete: values.valor_frete ? Number(values.valor_frete) : null,
      forma_pagamento: values.forma_pagamento || null,
      nf: values.nf || null,
    })
    success("Sucesso!", "Lote atualizado com sucesso")
    router.push({ name: "lotes.index" })
  },
})

// Computed para custo de aquisição (reativo)
const custoAquisicao = computed(() => {
  const valorTotal = Number(form.values.valor_total) || 0
  const valorFrete = Number(form.values.valor_frete) || 0
  return valorTotal + valorFrete
})

// Computed para custo unitário (reativo)
const custoUnitario = computed(() => {
  const quantidade = Number(form.values.quantidade_total) || 0
  if (quantidade <= 0) return 0
  return custoAquisicao.value / quantidade
})

function formatCurrency(value: number): string {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value)
}

onMounted(async () => {
  try {
    const [lotesResponse, tiposResponse] = await Promise.all([
      api.get<ApiResponse<Lote>>(`/lotes/${route.params.id}`),
      api.get<PaginatedResponse<TipoAtivo>>("/tipos-ativos", { per_page: 100 }),
    ])

    tiposAtivos.value = tiposResponse.data.map((t) => ({
      value: t.id,
      label: t.nome,
    }))

    const lote = lotesResponse.data
    form.setValues({
      codigo: lote.codigo,
      tipo_ativo_id: lote.tipo_ativo_id,
      quantidade_total: String(lote.quantidade_total),
      fornecedor: lote.fornecedor || "",
      valor_total: lote.valor_total ? String(lote.valor_total) : "",
      valor_frete: lote.valor_frete ? String(lote.valor_frete) : "",
      forma_pagamento: lote.forma_pagamento || "",
      nf: lote.nf || "",
      data_aquisicao: lote.data_aquisicao || "",
    })
  } catch (err) {
    error("Erro", "Lote nao encontrado")
    router.push({ name: "lotes.index" })
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" @click="router.back()">
        <ArrowLeft class="size-5" />
      </Button>
      <div>
        <h1 class="text-2xl font-bold">Editar Lote</h1>
        <p class="text-muted-foreground">Atualize os dados do lote</p>
      </div>
    </div>

    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <Spinner size="lg" />
    </div>

    <Card v-else class="max-w-2xl">
      <CardHeader>
        <CardTitle>Dados do Lote</CardTitle>
      </CardHeader>
      <CardContent>
        <form @submit="form.handleSubmit" class="space-y-4">
          <FormGroup>
            <FormField label="Codigo" required>
              <Input
                v-model="form.values.codigo"
                disabled
              />
            </FormField>

            <FormField label="Tipo de Ativo" required>
              <Select
                v-model="form.values.tipo_ativo_id"
                :options="tiposAtivos"
                disabled
              />
            </FormField>
          </FormGroup>

          <FormGroup>
            <FormField label="Quantidade Total" required>
              <Input
                type="number"
                v-model="form.values.quantidade_total"
                disabled
              />
            </FormField>

            <FormField label="Fornecedor" :error="form.getError('fornecedor')">
              <Input
                v-model="form.values.fornecedor"
                placeholder="Nome do fornecedor"
              />
            </FormField>
          </FormGroup>

          <FormGroup>
            <FormField label="Valor Total (R$)" :error="form.getError('valor_total')">
              <Input
                type="number"
                step="0.01"
                min="0"
                v-model="form.values.valor_total"
                placeholder="0,00"
              />
            </FormField>

            <FormField label="Valor Frete (R$)" :error="form.getError('valor_frete')">
              <Input
                type="number"
                step="0.01"
                min="0"
                v-model="form.values.valor_frete"
                placeholder="0,00"
              />
            </FormField>
          </FormGroup>

          <FormGroup>
            <FormField label="Forma de Pagamento" :error="form.getError('forma_pagamento')">
              <Select
                v-model="form.values.forma_pagamento"
                :options="formasPagamento"
                placeholder="Selecione"
              />
            </FormField>

            <FormField label="Nota Fiscal" :error="form.getError('nf')">
              <Input
                v-model="form.values.nf"
                placeholder="Numero da NF"
              />
            </FormField>
          </FormGroup>

          <FormField label="Data de Aquisicao">
            <Input type="date" v-model="form.values.data_aquisicao" disabled />
          </FormField>

          <!-- Custo de aquisição calculado -->
          <div v-if="custoAquisicao > 0" class="rounded-lg bg-muted p-4 space-y-2">
            <div class="flex justify-between items-center text-sm">
              <span class="text-muted-foreground">Custo de Aquisicao</span>
              <span class="font-medium">{{ formatCurrency(custoAquisicao) }}</span>
            </div>
            <div v-if="Number(form.values.quantidade_total) > 0" class="flex justify-between items-center pt-2 border-t">
              <span class="text-sm text-muted-foreground">Custo Unitario</span>
              <span class="text-lg font-semibold">{{ formatCurrency(custoUnitario) }}</span>
            </div>
          </div>

          <FormActions>
            <Button
              type="button"
              variant="outline"
              @click="router.push({ name: 'lotes.index' })"
            >
              Cancelar
            </Button>
            <Button type="submit" :disabled="form.isSubmitting.value">
              <Spinner v-if="form.isSubmitting.value" size="sm" class="mr-2" />
              Salvar
            </Button>
          </FormActions>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
