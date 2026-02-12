<script setup lang="ts">
import { ref, onMounted } from "vue"
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

    if (!values.codigo) errors.codigo = "Codigo e obrigatorio"
    if (!values.tipo_ativo_id) errors.tipo_ativo_id = "Tipo de ativo e obrigatorio"
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
    await api.put(`/lotes/${route.params.id}`, {
      ...values,
      quantidade_total: Number(values.quantidade_total),
      valor_unitario_diaria: Number(values.valor_unitario_diaria),
      custo_aquisicao: values.custo_aquisicao ? Number(values.custo_aquisicao) : null,
      data_aquisicao: values.data_aquisicao || null,
    })
    success("Sucesso!", "Lote atualizado com sucesso")
    router.push({ name: "lotes.index" })
  },
})

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
      valor_unitario_diaria: String(lote.valor_unitario_diaria),
      custo_aquisicao: lote.custo_aquisicao ? String(lote.custo_aquisicao) : "",
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
