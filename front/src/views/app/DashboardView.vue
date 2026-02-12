<script setup lang="ts">
import { ref, onMounted } from "vue"
import { useAuth } from "@/composables"
import { StatCard } from "@/components/app"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { Spinner } from "@/components/ui/spinner"
import { Users, Package, Boxes, FileText } from "lucide-vue-next"
import api from "@/lib/api"

const { userName, locador } = useAuth()

// Estado dos dados do dashboard
const stats = ref({
  locatarios: 0,
  tiposAtivos: 0,
  lotes: 0,
  contratos: 0,
})
const isLoading = ref(true)

// Carregar dados
onMounted(async () => {
  try {
    // TODO: Implementar endpoint de dashboard no backend
    // Por enquanto, dados mockados
    stats.value = {
      locatarios: 12,
      tiposAtivos: 8,
      lotes: 45,
      contratos: 23,
    }
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="space-y-6">
    <!-- Cabecalho -->
    <div>
      <h1 class="text-2xl font-bold">Dashboard</h1>
      <p class="text-muted-foreground">
        Bem-vindo de volta, {{ userName }}!
      </p>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <Spinner size="lg" />
    </div>

    <!-- Conteudo -->
    <template v-else>
      <!-- Cards de estatisticas -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <StatCard
          title="Locatarios"
          :value="stats.locatarios"
          description="Total cadastrados"
          :icon="Users"
        />
        <StatCard
          title="Tipos de Ativos"
          :value="stats.tiposAtivos"
          description="Categorias ativas"
          :icon="Package"
        />
        <StatCard
          title="Lotes"
          :value="stats.lotes"
          description="Em estoque"
          :icon="Boxes"
        />
        <StatCard
          title="Contratos"
          :value="stats.contratos"
          description="Ativos"
          :icon="FileText"
          trend="up"
          trend-value="+12%"
        />
      </div>

      <!-- Secao de atividades recentes -->
      <div class="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Contratos Recentes</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-muted-foreground text-sm">
              Nenhum contrato recente para exibir.
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Atividade</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-muted-foreground text-sm">
              Nenhuma atividade recente para exibir.
            </p>
          </CardContent>
        </Card>
      </div>
    </template>
  </div>
</template>
