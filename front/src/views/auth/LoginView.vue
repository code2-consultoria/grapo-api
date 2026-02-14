<script setup lang="ts">
import { RouterLink } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { FormField, FormActions } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import { useForm, useAuth } from "@/composables"
import type { LoginForm } from "@/types"

const { login } = useAuth()

const form = useForm<LoginForm>({
  initialValues: {
    email: "",
    password: "",
  },
  validate(values) {
    const errors: Partial<Record<keyof LoginForm, string>> = {}

    if (!values.email) {
      errors.email = "E-mail e obrigatorio"
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
      errors.email = "E-mail invalido"
    }

    if (!values.password) {
      errors.password = "Senha e obrigatoria"
    }

    return errors
  },
  async onSubmit(values) {
    await login(values.email, values.password)
  },
})
</script>

<template>
  <div>
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-foreground">Entrar</h2>
      <p class="text-muted-foreground mt-2">
        Acesse sua conta para gerenciar seus ativos
      </p>
    </div>

    <form @submit="form.handleSubmit" class="space-y-4">
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
          placeholder="Sua senha"
          :error="form.hasError('password')"
        />
      </FormField>

      <div class="text-right">
        <RouterLink
          :to="{ name: 'forgot-password' }"
          class="text-sm text-primary hover:underline"
        >
          Esqueci minha senha
        </RouterLink>
      </div>

      <FormActions class="pt-2">
        <Button type="submit" class="w-full" :disabled="form.isSubmitting.value">
          <Spinner v-if="form.isSubmitting.value" size="sm" />
          {{ form.isSubmitting.value ? "Entrando..." : "Entrar" }}
        </Button>
      </FormActions>

      <p class="text-center text-sm text-muted-foreground">
        Nao tem uma conta?
        <RouterLink :to="{ name: 'register' }" class="text-primary hover:underline">
          Cadastre-se
        </RouterLink>
      </p>
    </form>
  </div>
</template>
