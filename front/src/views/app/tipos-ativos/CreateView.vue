<script setup lang="ts">
import { useRouter } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { FormField, FormGroup, FormActions } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import { useForm, useNotification } from "@/composables"
import { ArrowLeft } from "lucide-vue-next"
import api from "@/lib/api"
import type { TipoAtivoForm } from "@/types"

const router = useRouter()
const { success } = useNotification()

const form = useForm<TipoAtivoForm>({
  initialValues: {
    nome: "",
    descricao: "",
    unidade_medida: "",
    valor_mensal_sugerido: "",
  },
  validate(values) {
    const errors: Partial<Record<keyof TipoAtivoForm, string>> = {}

    if (!values.nome) {
      errors.nome = "Nome e obrigatorio"
    }

    if (!values.unidade_medida) {
      errors.unidade_medida = "Unidade de medida e obrigatoria"
    }

    if (!values.valor_mensal_sugerido) {
      errors.valor_mensal_sugerido = "Valor mensal e obrigatorio"
    } else if (Number(values.valor_mensal_sugerido) <= 0) {
      errors.valor_mensal_sugerido = "Valor deve ser maior que zero"
    }

    return errors
  },
  async onSubmit(values) {
    await api.post("/tipos-ativos", {
      ...values,
      valor_mensal_sugerido: Number(values.valor_mensal_sugerido),
    })
    success("Sucesso!", "Tipo de ativo criado com sucesso")
    router.push({ name: "tipos-ativos.index" })
  },
})
</script>

<template>
  <div class="space-y-6">
    <!-- Cabecalho -->
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" @click="router.back()">
        <ArrowLeft class="size-5" />
      </Button>
      <div>
        <h1 class="text-2xl font-bold">Novo Tipo de Ativo</h1>
        <p class="text-muted-foreground">
          Cadastre uma nova categoria de ativo
        </p>
      </div>
    </div>

    <!-- Formulario -->
    <Card class="max-w-2xl">
      <CardHeader>
        <CardTitle>Dados do Tipo de Ativo</CardTitle>
      </CardHeader>
      <CardContent>
        <form @submit="form.handleSubmit" class="space-y-4">
          <FormField label="Nome" :error="form.getError('nome')" required>
            <Input
              v-model="form.values.nome"
              placeholder="Ex: Cadeira plastica"
              :error="form.hasError('nome')"
            />
          </FormField>

          <FormField label="Descricao" :error="form.getError('descricao')">
            <Input
              v-model="form.values.descricao"
              placeholder="Descricao opcional"
            />
          </FormField>

          <FormGroup>
            <FormField
              label="Unidade de Medida"
              :error="form.getError('unidade_medida')"
              required
            >
              <Input
                v-model="form.values.unidade_medida"
                placeholder="Ex: unidade, m², kg"
                :error="form.hasError('unidade_medida')"
              />
            </FormField>

            <FormField
              label="Valor Mensal Sugerido (R$)"
              :error="form.getError('valor_mensal_sugerido')"
              required
            >
              <Input
                type="number"
                step="0.01"
                min="0"
                v-model="form.values.valor_mensal_sugerido"
                placeholder="0,00"
                :error="form.hasError('valor_mensal_sugerido')"
              />
            </FormField>
          </FormGroup>

          <FormActions>
            <Button
              type="button"
              variant="outline"
              @click="router.push({ name: 'tipos-ativos.index' })"
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
