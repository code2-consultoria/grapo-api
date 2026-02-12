<script setup lang="ts">
import { ref, onMounted } from "vue"
import { useRouter, useRoute } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { FormField, FormGroup, FormActions } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import { useForm, useNotification } from "@/composables"
import { ArrowLeft } from "lucide-vue-next"
import api from "@/lib/api"
import type { Pessoa, PessoaForm, ApiResponse } from "@/types"

const router = useRouter()
const route = useRoute()
const { success, error } = useNotification()

const isLoading = ref(true)

const form = useForm<PessoaForm>({
  initialValues: {
    tipo: "locatario",
    nome: "",
    email: "",
    telefone: "",
    endereco: "",
  },
  validate(values) {
    const errors: Partial<Record<keyof PessoaForm, string>> = {}

    if (!values.nome) {
      errors.nome = "Nome e obrigatorio"
    }

    if (values.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
      errors.email = "E-mail invalido"
    }

    return errors
  },
  async onSubmit(values) {
    await api.put(`/pessoas/${route.params.id}`, values)
    success("Sucesso!", "Locatario atualizado com sucesso")
    router.push({ name: "locatarios.index" })
  },
})

onMounted(async () => {
  try {
    const response = await api.get<ApiResponse<Pessoa>>(
      `/pessoas/${route.params.id}`,
    )
    const pessoa = response.data

    form.setValues({
      tipo: pessoa.tipo,
      nome: pessoa.nome,
      email: pessoa.email || "",
      telefone: pessoa.telefone || "",
      endereco: pessoa.endereco || "",
    })
  } catch (err) {
    error("Erro", "Locatario nao encontrado")
    router.push({ name: "locatarios.index" })
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
        <h1 class="text-2xl font-bold">Editar Locatario</h1>
        <p class="text-muted-foreground">Atualize os dados do locatario</p>
      </div>
    </div>

    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <Spinner size="lg" />
    </div>

    <Card v-else class="max-w-2xl">
      <CardHeader>
        <CardTitle>Dados do Locatario</CardTitle>
      </CardHeader>
      <CardContent>
        <form @submit="form.handleSubmit" class="space-y-4">
          <FormField label="Nome" :error="form.getError('nome')" required>
            <Input
              v-model="form.values.nome"
              placeholder="Nome completo ou razao social"
              :error="form.hasError('nome')"
            />
          </FormField>

          <FormGroup>
            <FormField label="E-mail" :error="form.getError('email')">
              <Input
                type="email"
                v-model="form.values.email"
                placeholder="email@exemplo.com"
                :error="form.hasError('email')"
              />
            </FormField>

            <FormField label="Telefone" :error="form.getError('telefone')">
              <Input
                v-model="form.values.telefone"
                placeholder="(00) 00000-0000"
              />
            </FormField>
          </FormGroup>

          <FormField label="Endereco" :error="form.getError('endereco')">
            <Input
              v-model="form.values.endereco"
              placeholder="Endereco completo"
            />
          </FormField>

          <FormActions>
            <Button
              type="button"
              variant="outline"
              @click="router.push({ name: 'locatarios.index' })"
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
