<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import { useRouter } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Select } from "@/components/ui/select"
import { Combobox } from "@/components/ui/combobox"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { Dialog, DialogFooter } from "@/components/ui/dialog"
import { FormField, FormGroup, FormActions } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import { useForm, useNotification } from "@/composables"
import { ArrowLeft } from "lucide-vue-next"
import api from "@/lib/api"
import type { LoteForm, TipoAtivo, TipoAtivoForm, PaginatedResponse } from "@/types"

const router = useRouter()
const { success } = useNotification()

const ativos = ref<{ value: string; label: string }[]>([])
const loadingAtivos = ref(true)

const formasPagamento = [
  { value: "pix", label: "PIX" },
  { value: "boleto", label: "Boleto" },
  { value: "cartao", label: "Cartao" },
  { value: "transferencia", label: "Transferencia" },
  { value: "dinheiro", label: "Dinheiro" },
  { value: "outro", label: "Outro" },
]

// Estado do dialog de criar ativo
const showAtivoDialog = ref(false)
const ativoForm = useForm<TipoAtivoForm>({
  initialValues: {
    nome: "",
    descricao: "",
    unidade_medida: "un",
    valor_mensal_sugerido: 0,
  },
  validate(values) {
    const errors: Partial<Record<keyof TipoAtivoForm, string>> = {}
    if (!values.nome) {
      errors.nome = "Nome e obrigatorio"
    }
    if (!values.unidade_medida) {
      errors.unidade_medida = "Unidade de medida e obrigatoria"
    }
    return errors
  },
  async onSubmit(values) {
    const response = await api.post<{ data: TipoAtivo }>("/tipos-ativos", values)
    success("Sucesso!", "Ativo criado")

    const newAtivo = response.data
    ativos.value.push({
      value: newAtivo.id,
      label: newAtivo.nome,
    })
    form.values.tipo_ativo_id = newAtivo.id

    showAtivoDialog.value = false
    ativoForm.reset()
  },
})

function openAtivoDialog() {
  ativoForm.reset()
  showAtivoDialog.value = true
}

// Carregar ativos
onMounted(async () => {
  try {
    const response = await api.get<PaginatedResponse<TipoAtivo>>("/tipos-ativos", {
      per_page: 100,
    })
    ativos.value = response.data.map((t) => ({
      value: t.id,
      label: t.nome,
    }))
  } finally {
    loadingAtivos.value = false
  }
})

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

    if (!values.codigo) {
      errors.codigo = "Codigo e obrigatorio"
    }

    if (!values.tipo_ativo_id) {
      errors.tipo_ativo_id = "Ativo e obrigatorio"
    }

    if (!values.quantidade_total) {
      errors.quantidade_total = "Quantidade e obrigatoria"
    } else if (Number(values.quantidade_total) <= 0) {
      errors.quantidade_total = "Quantidade deve ser maior que zero"
    }

    return errors
  },
  async onSubmit(values) {
    await api.post("/lotes", {
      ...values,
      quantidade_total: Number(values.quantidade_total),
      valor_total: values.valor_total ? Number(values.valor_total) : null,
      valor_frete: values.valor_frete ? Number(values.valor_frete) : null,
      fornecedor: values.fornecedor || null,
      forma_pagamento: values.forma_pagamento || null,
      nf: values.nf || null,
      data_aquisicao: values.data_aquisicao || null,
    })
    success("Sucesso!", "Lote criado com sucesso")
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
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" @click="router.back()">
        <ArrowLeft class="size-5" />
      </Button>
      <div>
        <h1 class="text-2xl font-bold">Novo Lote</h1>
        <p class="text-muted-foreground">Cadastre um novo lote de ativos</p>
      </div>
    </div>

    <Card class="max-w-2xl">
      <CardHeader>
        <CardTitle>Dados do Lote</CardTitle>
      </CardHeader>
      <CardContent>
        <form @submit="form.handleSubmit" class="space-y-4">
          <FormGroup>
            <FormField label="Codigo" :error="form.getError('codigo')" required>
              <Input
                v-model="form.values.codigo"
                placeholder="Ex: LOT-001"
                :error="form.hasError('codigo')"
              />
            </FormField>

            <FormField
              label="Ativo"
              :error="form.getError('tipo_ativo_id')"
              required
            >
              <Combobox
                v-model="form.values.tipo_ativo_id"
                :options="ativos"
                :disabled="loadingAtivos"
                placeholder="Buscar ativo..."
                search-placeholder="Digite para buscar..."
                empty-text="Nenhum ativo encontrado"
                allow-create
                create-text="Criar novo ativo"
                @create="openAtivoDialog"
              />
            </FormField>
          </FormGroup>

          <FormGroup>
            <FormField
              label="Quantidade Total"
              :error="form.getError('quantidade_total')"
              required
            >
              <Input
                type="number"
                min="1"
                v-model="form.values.quantidade_total"
                placeholder="0"
                :error="form.hasError('quantidade_total')"
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

          <FormField label="Data de Aquisicao" :error="form.getError('data_aquisicao')">
            <Input type="date" v-model="form.values.data_aquisicao" />
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

    <!-- Dialog Criar Ativo -->
    <Dialog
      v-model:open="showAtivoDialog"
      title="Novo Ativo"
      description="Cadastre um novo ativo rapidamente"
    >
      <form @submit="ativoForm.handleSubmit" class="space-y-4">
        <FormField label="Nome" :error="ativoForm.getError('nome')" required>
          <Input
            v-model="ativoForm.values.nome"
            placeholder="Ex: Cadeira, Mesa, Tenda"
            :error="ativoForm.hasError('nome')"
          />
        </FormField>

        <FormField label="Descricao" :error="ativoForm.getError('descricao')">
          <Input
            v-model="ativoForm.values.descricao"
            placeholder="Descricao do ativo"
          />
        </FormField>

        <FormGroup>
          <FormField label="Unidade de Medida" :error="ativoForm.getError('unidade_medida')" required>
            <Input
              v-model="ativoForm.values.unidade_medida"
              placeholder="un, m, m2, etc"
              :error="ativoForm.hasError('unidade_medida')"
            />
          </FormField>

          <FormField label="Valor Mensal Sugerido (R$)" :error="ativoForm.getError('valor_mensal_sugerido')">
            <Input
              type="number"
              v-model.number="ativoForm.values.valor_mensal_sugerido"
              min="0"
              step="0.01"
            />
          </FormField>
        </FormGroup>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="showAtivoDialog = false"
          >
            Cancelar
          </Button>
          <Button type="submit" :disabled="ativoForm.isSubmitting.value">
            <Spinner v-if="ativoForm.isSubmitting.value" size="sm" class="mr-2" />
            Salvar
          </Button>
        </DialogFooter>
      </form>
    </Dialog>
  </div>
</template>
