<script setup lang="ts">
import { ref, onMounted } from "vue"
import { RouterLink, useRouter } from "vue-router"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { FormField, FormActions } from "@/components/forms"
import { Spinner } from "@/components/ui/spinner"
import { Badge } from "@/components/ui/badge"
import { useForm, useNotification } from "@/composables"
import { useAuthStore } from "@/stores/auth"
import { Check } from "lucide-vue-next"
import api from "@/lib/api"
import type { RegisterForm } from "@/types"

interface Plano {
  id: string
  nome: string
  duracao_meses: number
  valor: string
  ativo: boolean
}

const router = useRouter()
const authStore = useAuthStore()
const { success, error: showError } = useNotification()

const planos = ref<Plano[]>([])
const selectedPlano = ref<string | null>(null)
const isLoadingPlanos = ref(true)

onMounted(async () => {
  try {
    const response = await api.get<{ data: Plano[] }>("/planos")
    planos.value = response.data.filter((p) => p.ativo)
  } catch {
    // Silently fail - usuario pode criar conta sem plano
  } finally {
    isLoadingPlanos.value = false
  }
})

function formatCurrency(value: string | number): string {
  const num = typeof value === "string" ? parseFloat(value) : value
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(num)
}

function getDuracaoLabel(meses: number): string {
  switch (meses) {
    case 3:
      return "Trimestral"
    case 6:
      return "Semestral"
    case 12:
      return "Anual"
    default:
      return `${meses} meses`
  }
}

function getValorMensal(valor: string, meses: number): string {
  const valorNum = parseFloat(valor)
  return formatCurrency(valorNum / meses)
}

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
    const response = await api.post<{
      data: {
        token: string
        user: { id: number; name: string; email: string }
        locador: { id: string; nome: string; email: string }
      }
    }>("/auth/register", values)

    // Salva token e dados do usuario (cast parcial, fetchMe completa depois)
    authStore.setAuthData(
      response.data.token,
      response.data.user as Parameters<typeof authStore.setAuthData>[1],
      response.data.locador as Parameters<typeof authStore.setAuthData>[2]
    )

    // Se selecionou um plano, redireciona para checkout
    if (selectedPlano.value) {
      try {
        const checkoutResponse = await api.post<{ checkout_url: string }>("/assinaturas/checkout", {
          plano_id: selectedPlano.value,
          success_url: `${window.location.origin}/app/dashboard?assinatura=sucesso`,
          cancel_url: `${window.location.origin}/app/dashboard?assinatura=cancelada`,
        })

        // Redireciona para o Stripe
        window.location.href = checkoutResponse.checkout_url
        return
      } catch {
        showError("Erro", "Nao foi possivel iniciar o checkout. Voce pode assinar depois no perfil.")
      }
    }

    success("Conta criada!", "Voce tem 7 dias de teste gratuito.")
    router.push({ name: "dashboard" })
  },
})
</script>

<template>
  <div>
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-foreground">Criar conta</h2>
      <p class="text-muted-foreground mt-2">Cadastre-se para comecar a gerenciar seus ativos</p>
    </div>

    <form @submit="form.handleSubmit" class="space-y-6">
      <!-- Dados da conta -->
      <div class="space-y-4">
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

        <FormField label="Confirmar senha" :error="form.getError('password_confirmation')" required>
          <Input
            type="password"
            v-model="form.values.password_confirmation"
            placeholder="Repita sua senha"
            :error="form.hasError('password_confirmation')"
          />
        </FormField>
      </div>

      <!-- Selecao de Plano -->
      <div class="border-t pt-6">
        <h3 class="text-lg font-medium mb-2">Escolha seu plano</h3>
        <p class="text-sm text-muted-foreground mb-4">
          Selecione um plano ou comece com 7 dias de teste gratuito
        </p>

        <div v-if="isLoadingPlanos" class="flex justify-center py-4">
          <Spinner />
        </div>

        <div v-else class="grid gap-3">
          <!-- Opcao Trial -->
          <button
            type="button"
            class="relative flex items-center justify-between p-4 rounded-lg border-2 transition-colors text-left"
            :class="
              selectedPlano === null
                ? 'border-primary bg-primary/5'
                : 'border-border hover:border-muted-foreground/50'
            "
            @click="selectedPlano = null"
          >
            <div>
              <p class="font-medium">Teste gratuito</p>
              <p class="text-sm text-muted-foreground">7 dias para experimentar</p>
            </div>
            <Badge variant="secondary">Gratis</Badge>
            <div
              v-if="selectedPlano === null"
              class="absolute -top-2 -right-2 size-6 rounded-full bg-primary flex items-center justify-center"
            >
              <Check class="size-4 text-primary-foreground" />
            </div>
          </button>

          <!-- Planos -->
          <button
            v-for="plano in planos"
            :key="plano.id"
            type="button"
            class="relative flex items-center justify-between p-4 rounded-lg border-2 transition-colors text-left"
            :class="
              selectedPlano === plano.id
                ? 'border-primary bg-primary/5'
                : 'border-border hover:border-muted-foreground/50'
            "
            @click="selectedPlano = plano.id"
          >
            <div>
              <p class="font-medium">{{ getDuracaoLabel(plano.duracao_meses) }}</p>
              <p class="text-sm text-muted-foreground">
                {{ getValorMensal(plano.valor, plano.duracao_meses) }}/mes
              </p>
            </div>
            <div class="text-right">
              <p class="font-semibold">{{ formatCurrency(plano.valor) }}</p>
              <p class="text-xs text-muted-foreground">total</p>
            </div>
            <div
              v-if="selectedPlano === plano.id"
              class="absolute -top-2 -right-2 size-6 rounded-full bg-primary flex items-center justify-center"
            >
              <Check class="size-4 text-primary-foreground" />
            </div>
          </button>
        </div>
      </div>

      <FormActions class="pt-2">
        <Button type="submit" class="w-full" :disabled="form.isSubmitting.value">
          <Spinner v-if="form.isSubmitting.value" size="sm" />
          {{ form.isSubmitting.value ? "Criando conta..." : "Criar conta" }}
        </Button>
      </FormActions>

      <p class="text-center text-sm text-muted-foreground">
        Ja tem uma conta?
        <RouterLink :to="{ name: 'login' }" class="text-primary hover:underline"> Entrar </RouterLink>
      </p>
    </form>
  </div>
</template>
