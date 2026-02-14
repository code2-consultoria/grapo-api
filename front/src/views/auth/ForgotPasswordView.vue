<script setup lang="ts">
import { ref } from "vue"
import { RouterLink } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { FormField, FormActions } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import { useForm, useNotification } from "@/composables"
import api from "@/lib/api"

interface ForgotPasswordForm {
  email: string
}

const { showSuccess, showError } = useNotification()
const emailSent = ref(false)

const form = useForm<ForgotPasswordForm>({
  initialValues: {
    email: "",
  },
  validate(values) {
    const errors: Partial<Record<keyof ForgotPasswordForm, string>> = {}

    if (!values.email) {
      errors.email = "E-mail e obrigatorio"
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
      errors.email = "E-mail invalido"
    }

    return errors
  },
  async onSubmit(values) {
    try {
      await api.post("/auth/forgot-password", values)
      emailSent.value = true
      showSuccess("Email enviado com sucesso!")
    } catch (error: unknown) {
      const apiError = error as { message?: string }
      showError(apiError.message || "Erro ao enviar email")
    }
  },
})
</script>

<template>
  <div>
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-foreground">Recuperar Senha</h2>
      <p class="text-muted-foreground mt-2">
        Informe seu e-mail para receber o link de recuperacao
      </p>
    </div>

    <!-- Mensagem de sucesso -->
    <div v-if="emailSent" class="space-y-4">
      <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <p class="text-green-800 dark:text-green-200 text-sm">
          Se o e-mail estiver cadastrado, voce recebera um link para redefinir sua senha.
          Verifique sua caixa de entrada e spam.
        </p>
      </div>

      <RouterLink :to="{ name: 'login' }">
        <Button variant="outline" class="w-full">
          Voltar para o login
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

      <FormActions class="pt-2">
        <Button type="submit" class="w-full" :disabled="form.isSubmitting.value">
          <Spinner v-if="form.isSubmitting.value" size="sm" />
          {{ form.isSubmitting.value ? "Enviando..." : "Enviar link de recuperacao" }}
        </Button>
      </FormActions>

      <p class="text-center text-sm text-muted-foreground">
        Lembrou sua senha?
        <RouterLink :to="{ name: 'login' }" class="text-primary hover:underline">
          Voltar ao login
        </RouterLink>
      </p>
    </form>
  </div>
</template>
