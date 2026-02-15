<script setup lang="ts">
import { ref } from "vue"
import { RouterLink, useRoute, useRouter } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { FormField, FormActions } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import { useForm, useNotification } from "@/composables"
import api from "@/lib/api"

interface ResetPasswordForm {
  email: string
  password: string
  password_confirmation: string
}

const route = useRoute()
const router = useRouter()
const { success, error: showError } = useNotification()

const token = route.params.token as string
const emailFromQuery = (route.query.email as string) || ""
const resetSuccess = ref(false)

const form = useForm<ResetPasswordForm>({
  initialValues: {
    email: emailFromQuery,
    password: "",
    password_confirmation: "",
  },
  validate(values) {
    const errors: Partial<Record<keyof ResetPasswordForm, string>> = {}

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
      errors.password_confirmation = "Confirmacao e obrigatoria"
    } else if (values.password !== values.password_confirmation) {
      errors.password_confirmation = "Senhas nao conferem"
    }

    return errors
  },
  async onSubmit(values) {
    try {
      await api.post("/auth/reset-password", {
        token,
        email: values.email,
        password: values.password,
        password_confirmation: values.password_confirmation,
      })
      resetSuccess.value = true
      success("Senha alterada com sucesso!")

      // Redireciona para login apos 3 segundos
      setTimeout(() => {
        router.push({ name: "login" })
      }, 3000)
    } catch (error: unknown) {
      const apiError = error as { message?: string; errors?: Record<string, string[]> }
      if (apiError.errors?.email?.[0]) {
        showError(apiError.errors.email[0])
      } else {
        showError(apiError.message || "Erro ao alterar senha")
      }
    }
  },
})
</script>

<template>
  <div>
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-foreground">Redefinir Senha</h2>
      <p class="text-muted-foreground mt-2">
        Defina sua nova senha
      </p>
    </div>

    <!-- Mensagem de sucesso -->
    <div v-if="resetSuccess" class="space-y-4">
      <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <p class="text-green-800 dark:text-green-200 text-sm">
          Sua senha foi alterada com sucesso! Voce sera redirecionado para o login.
        </p>
      </div>

      <RouterLink :to="{ name: 'login' }">
        <Button class="w-full">
          Ir para o login
        </Button>
      </RouterLink>
    </div>

    <!-- Formulario -->
    <form v-else @submit="form.handleSubmit" class="space-y-4">
      <FormField label="E-mail" :error="form.getError('email')" required>
        <Input
          type="email"
          v-model="form.values.email"
          placeholder="seu@email.com"
          :error="form.hasError('email')"
        />
      </FormField>

      <FormField label="Nova Senha" :error="form.getError('password')" required>
        <Input
          type="password"
          v-model="form.values.password"
          placeholder="Minimo 8 caracteres"
          :error="form.hasError('password')"
        />
      </FormField>

      <FormField label="Confirmar Senha" :error="form.getError('password_confirmation')" required>
        <Input
          type="password"
          v-model="form.values.password_confirmation"
          placeholder="Repita a senha"
          :error="form.hasError('password_confirmation')"
        />
      </FormField>

      <FormActions class="pt-2">
        <Button type="submit" class="w-full" :disabled="form.isSubmitting.value">
          <Spinner v-if="form.isSubmitting.value" size="sm" />
          {{ form.isSubmitting.value ? "Salvando..." : "Redefinir Senha" }}
        </Button>
      </FormActions>

      <p class="text-center text-sm text-muted-foreground">
        <RouterLink :to="{ name: 'login' }" class="text-primary hover:underline">
          Voltar ao login
        </RouterLink>
      </p>
    </form>
  </div>
</template>
