<script setup lang="ts">
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Select } from "@/components/ui/select"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { FormField, FormGroup, FormActions } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import { useForm, useNotification } from "@/composables"
import { ArrowLeft } from "lucide-vue-next"
import api from "@/lib/api"
import type { LoteForm, TipoAtivo, PaginatedResponse } from "@/types"

const router = useRouter()
const { success } = useNotification()

const tiposAtivos = ref<{ value: string; label: string }[]>([])
const loadingTipos = ref(true)

// Carregar tipos de ativos
onMounted(async () => {
  try {
    const response = await api.get<PaginatedResponse<TipoAtivo>>("/tipos-ativos", {
      per_page: 100,
    })
    tiposAtivos.value = response.data.map((t) => ({
      value: t.id,
      label: t.nome,
    }))
  } finally {
    loadingTipos.value = false
  }
})

const form = useForm<LoteForm>({
  initialValues: {
    codigo: "",
    tipo_ativo_id: "",
    quantidade_total: "",
    valor_unitario_diaria: "",
    custo_aquisicao: "",
    data_aquisicao: "",
  },
  validate(values) {
    const errors: Partial<Record<keyof LoteForm, string>> = {}

    if (!values.codigo) {
      errors.codigo = "Codigo e obrigatorio"
    }

    if (!values.tipo_ativo_id) {
      errors.tipo_ativo_id = "Tipo de ativo e obrigatorio"
    }

    if (!values.quantidade_total) {
      errors.quantidade_total = "Quantidade e obrigatoria"
    } else if (Number(values.quantidade_total) <= 0) {
      errors.quantidade_total = "Quantidade deve ser maior que zero"
    }

    if (!values.valor_unitario_diaria) {
      errors.valor_unitario_diaria = "Valor da diaria e obrigatorio"
    } else if (Number(values.valor_unitario_diaria) <= 0) {
      errors.valor_unitario_diaria = "Valor deve ser maior que zero"
    }

    return errors
  },
  async onSubmit(values) {
    await api.post("/lotes", {
      ...values,
      quantidade_total: Number(values.quantidade_total),
      valor_unitario_diaria: Number(values.valor_unitario_diaria),
      custo_aquisicao: values.custo_aquisicao
        ? Number(values.custo_aquisicao)
        : null,
      data_aquisicao: values.data_aquisicao || null,
    })
    success("Sucesso!", "Lote criado com sucesso")
    router.push({ name: "lotes.index" })
  },
})
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
              label="Tipo de Ativo"
              :error="form.getError('tipo_ativo_id')"
              required
            >
              <Select
                v-model="form.values.tipo_ativo_id"
                :options="tiposAtivos"
                :disabled="loadingTipos"
                placeholder="Selecione o tipo"
                :error="form.hasError('tipo_ativo_id')"
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

            <FormField
              label="Valor Diaria (R$)"
              :error="form.getError('valor_unitario_diaria')"
              required
            >
              <Input
                type="number"
                step="0.01"
                min="0"
                v-model="form.values.valor_unitario_diaria"
                placeholder="0,00"
                :error="form.hasError('valor_unitario_diaria')"
              />
            </FormField>
          </FormGroup>

          <FormGroup>
            <FormField
              label="Custo de Aquisicao (R$)"
              :error="form.getError('custo_aquisicao')"
            >
              <Input
                type="number"
                step="0.01"
                min="0"
                v-model="form.values.custo_aquisicao"
                placeholder="0,00"
              />
            </FormField>

            <FormField
              label="Data de Aquisicao"
              :error="form.getError('data_aquisicao')"
            >
              <Input type="date" v-model="form.values.data_aquisicao" />
            </FormField>
          </FormGroup>

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
