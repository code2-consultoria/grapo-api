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
import type { ContratoForm, Pessoa, PaginatedResponse } from "@/types"

const router = useRouter()
const { success } = useNotification()

const locatarios = ref<{ value: string; label: string }[]>([])
const loadingLocatarios = ref(true)

onMounted(async () => {
  try {
    const response = await api.get<PaginatedResponse<Pessoa>>("/pessoas", {
      per_page: 100,
      // tipo: 'locatario',
    })
    locatarios.value = response.data.map((p) => ({
      value: p.id,
      label: p.nome,
    }))
  } finally {
    loadingLocatarios.value = false
  }
})

const form = useForm<ContratoForm>({
  initialValues: {
    locatario_id: "",
    data_inicio: "",
    data_termino: "",
    observacoes: "",
  },
  validate(values) {
    const errors: Partial<Record<keyof ContratoForm, string>> = {}

    if (!values.locatario_id) {
      errors.locatario_id = "Locatario e obrigatorio"
    }

    if (!values.data_inicio) {
      errors.data_inicio = "Data de inicio e obrigatoria"
    }

    if (!values.data_termino) {
      errors.data_termino = "Data de termino e obrigatoria"
    } else if (values.data_inicio && values.data_termino < values.data_inicio) {
      errors.data_termino = "Data de termino deve ser posterior a data de inicio"
    }

    return errors
  },
  async onSubmit(values) {
    const response = await api.post<{ data: { id: string } }>("/contratos", values)
    success("Sucesso!", "Contrato criado com sucesso")
    router.push({ name: "contratos.show", params: { id: response.data.id } })
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
        <h1 class="text-2xl font-bold">Novo Contrato</h1>
        <p class="text-muted-foreground">Crie um novo contrato de locacao</p>
      </div>
    </div>

    <Card class="max-w-2xl">
      <CardHeader>
        <CardTitle>Dados do Contrato</CardTitle>
      </CardHeader>
      <CardContent>
        <form @submit="form.handleSubmit" class="space-y-4">
          <FormField
            label="Locatario"
            :error="form.getError('locatario_id')"
            required
          >
            <Select
              v-model="form.values.locatario_id"
              :options="locatarios"
              :disabled="loadingLocatarios"
              placeholder="Selecione o locatario"
              :error="form.hasError('locatario_id')"
            />
          </FormField>

          <FormGroup>
            <FormField
              label="Data de Inicio"
              :error="form.getError('data_inicio')"
              required
            >
              <Input
                type="date"
                v-model="form.values.data_inicio"
                :error="form.hasError('data_inicio')"
              />
            </FormField>

            <FormField
              label="Data de Termino"
              :error="form.getError('data_termino')"
              required
            >
              <Input
                type="date"
                v-model="form.values.data_termino"
                :error="form.hasError('data_termino')"
              />
            </FormField>
          </FormGroup>

          <FormField label="Observacoes" :error="form.getError('observacoes')">
            <Input
              v-model="form.values.observacoes"
              placeholder="Observacoes opcionais"
            />
          </FormField>

          <FormActions>
            <Button
              type="button"
              variant="outline"
              @click="router.push({ name: 'contratos.index' })"
            >
              Cancelar
            </Button>
            <Button type="submit" :disabled="form.isSubmitting.value">
              <Spinner v-if="form.isSubmitting.value" size="sm" class="mr-2" />
              Criar Contrato
            </Button>
          </FormActions>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
