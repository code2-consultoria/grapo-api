<script setup lang="ts">
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Combobox } from "@/components/ui/combobox"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { Dialog, DialogFooter } from "@/components/ui/dialog"
import { FormField, FormGroup, FormActions } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import { useForm, useNotification } from "@/composables"
import { ArrowLeft } from "lucide-vue-next"
import api from "@/lib/api"
import type { ContratoForm, PessoaForm, Pessoa, PaginatedResponse } from "@/types"

const router = useRouter()
const { success } = useNotification()

const locatarios = ref<{ value: string; label: string }[]>([])
const loadingLocatarios = ref(true)
const showLocatarioDialog = ref(false)

async function loadLocatarios() {
  loadingLocatarios.value = true
  try {
    const response = await api.get<PaginatedResponse<Pessoa>>("/pessoas", {
      per_page: 100,
    })
    locatarios.value = response.data.map((p) => ({
      value: p.id,
      label: p.nome,
    }))
  } finally {
    loadingLocatarios.value = false
  }
}

onMounted(loadLocatarios)

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
    } else if (values.data_inicio && values.data_termino <= values.data_inicio) {
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

// Form para criar locatario inline
const locatarioForm = useForm<PessoaForm>({
  initialValues: {
    tipo: "locatario",
    nome: "",
    documento: "",
    email: "",
    telefone: "",
    endereco: "",
  },
  validate(values) {
    const errors: Partial<Record<keyof PessoaForm, string>> = {}
    if (!values.nome) {
      errors.nome = "Nome e obrigatorio"
    }
    if (!values.documento) {
      errors.documento = "CPF ou CNPJ e obrigatorio"
    }
    if (values.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
      errors.email = "E-mail invalido"
    }
    return errors
  },
  async onSubmit(values) {
    const response = await api.post<{ data: Pessoa }>("/pessoas", values)
    success("Sucesso!", "Locatario criado com sucesso")

    // Adiciona o novo locatario a lista e seleciona
    const newLocatario = response.data
    locatarios.value.push({
      value: newLocatario.id,
      label: newLocatario.nome,
    })
    form.setFieldValue("locatario_id", newLocatario.id)

    // Fecha o dialog e reseta o form
    showLocatarioDialog.value = false
    locatarioForm.reset()
  },
})

function openLocatarioDialog() {
  locatarioForm.reset()
  showLocatarioDialog.value = true
}
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
            <Combobox
              v-model="form.values.locatario_id"
              :options="locatarios"
              :disabled="loadingLocatarios"
              :loading="loadingLocatarios"
              placeholder="Buscar locatario..."
              search-placeholder="Digite para buscar..."
              empty-text="Nenhum locatario encontrado"
              :error="form.hasError('locatario_id')"
              allow-create
              create-text="Criar novo locatario"
              @create="openLocatarioDialog"
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

    <!-- Modal criar locatario -->
    <Dialog
      v-model:open="showLocatarioDialog"
      title="Novo Locatario"
      description="Cadastre um novo locatario rapidamente"
    >
      <form @submit="locatarioForm.handleSubmit" class="space-y-4">
        <FormField label="Nome" :error="locatarioForm.getError('nome')" required>
          <Input
            v-model="locatarioForm.values.nome"
            placeholder="Nome completo ou razao social"
            :error="locatarioForm.hasError('nome')"
          />
        </FormField>

        <FormField label="CPF ou CNPJ" :error="locatarioForm.getError('documento')" required>
          <Input
            v-model="locatarioForm.values.documento"
            placeholder="Digite apenas numeros"
            :error="locatarioForm.hasError('documento')"
          />
        </FormField>

        <FormGroup>
          <FormField label="E-mail" :error="locatarioForm.getError('email')">
            <Input
              type="email"
              v-model="locatarioForm.values.email"
              placeholder="email@exemplo.com"
              :error="locatarioForm.hasError('email')"
            />
          </FormField>

          <FormField label="Telefone" :error="locatarioForm.getError('telefone')">
            <Input
              v-model="locatarioForm.values.telefone"
              placeholder="(00) 00000-0000"
            />
          </FormField>
        </FormGroup>

        <FormField label="Endereco" :error="locatarioForm.getError('endereco')">
          <Input
            v-model="locatarioForm.values.endereco"
            placeholder="Endereco completo"
          />
        </FormField>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="showLocatarioDialog = false"
          >
            Cancelar
          </Button>
          <Button type="submit" :disabled="locatarioForm.isSubmitting.value">
            <Spinner v-if="locatarioForm.isSubmitting.value" size="sm" class="mr-2" />
            Salvar Locatario
          </Button>
        </DialogFooter>
      </form>
    </Dialog>
  </div>
</template>
