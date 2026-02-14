<script setup lang="ts">
import { RouterLink, useRouter } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { FormField, FormActions } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import { useForm, useNotification } from "@/composables"
import { useAuthStore } from "@/stores/auth"
import api from "@/lib/api"
import type { RegisterForm } from "@/types"

const router = useRouter()
const authStore = useAuthStore()
const { showSuccess } = useNotification()

const form = useForm<RegisterForm>({
  initialValues: {
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
  },
  validate(values) {
    const errors: Partial<Record<keyof RegisterForm, string>> = {}

    if (!values.name) {
      errors.name = "Nome e obrigatorio"
    } else if (values.name.length < 3) {
      errors.name = "Nome deve ter pelo menos 3 caracteres"
    }

    if (!values.email) {
      errors.email = "E-mail e obrigatorio"
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
      errors.email = "E-mail invalido"
    }

    if (!values.password) {
      errors.password = "Senha e obrigatoria"
    } else if (values.password.length < 8) {
      errors.password = "Senha deve ter pelo menos 8 caracteres"
    }

    if (!values.password_confirmation) {
      errors.password_confirmation = "Confirmacao de senha e obrigatoria"
    } else if (values.password !== values.password_confirmation) {
      errors.password_confirmation = "Senhas nao conferem"
    }

    return errors
  },
  async onSubmit(values) {
    const response = await api.post<{ data: { token: string; user: { id: number; name: string; email: string }; locador: { id: string; nome: string; email: string } } }>("/auth/register", values)

    // Salva token e dados do usuario
    authStore.setAuthData(
      response.data.token,
      response.data.user,
      response.data.locador
    )

    showSuccess("Conta criada com sucesso!")
    router.push({ name: "dashboard" })
  },
})
</script>

<template>
  <div>
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-foreground">Criar conta</h2>
      <p class="text-muted-foreground mt-2">
        Cadastre-se para comecar a gerenciar seus ativos
      </p>
    </div>

    <form @submit="form.handleSubmit" class="space-y-4">
      <FormField label="Nome" :error="form.getError('name')" required>
        <Input
          type="text"
          v-model="form.values.name"
          placeholder="Seu nome completo"
          :error="form.hasError('name')"
        />
      </FormField>

      <FormField label="E-mail" :error="form.getError('email')" required>
        <Input
          type="email"
          v-model="form.values.email"
          placeholder="seu@email.com"
          :error="form.hasError('email')"
        />
      </FormField>

      <FormField label="Senha" :error="form.getError('password')" required>
        <Input
          type="password"
          v-model="form.values.password"
          placeholder="Minimo 8 caracteres"
          :error="form.hasError('password')"
        />
      </FormField>

      <FormField
        label="Confirmar senha"
        :error="form.getError('password_confirmation')"
        required
      >
        <Input
          type="password"
          v-model="form.values.password_confirmation"
          placeholder="Repita sua senha"
          :error="form.hasError('password_confirmation')"
        />
      </FormField>

      <FormActions class="pt-2">
        <Button type="submit" class="w-full" :disabled="form.isSubmitting.value">
          <Spinner v-if="form.isSubmitting.value" size="sm" />
          {{ form.isSubmitting.value ? "Criando conta..." : "Criar conta" }}
        </Button>
      </FormActions>

      <p class="text-center text-sm text-muted-foreground">
        Ja tem uma conta?
        <RouterLink :to="{ name: 'login' }" class="text-primary hover:underline">
          Entrar
        </RouterLink>
      </p>
    </form>
  </div>
</template>
