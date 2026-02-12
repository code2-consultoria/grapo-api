<script setup lang="ts">
import { RouterLink } from "vue-router"
import { Button } from "@/components/ui/button"
import { Home, ArrowLeft } from "lucide-vue-next"
</script>

<template>
  <div class="min-h-screen bg-background flex items-center justify-center p-8">
    <div class="text-center">
      <h1 class="text-9xl font-bold text-primary/20">404</h1>
      <h2 class="text-2xl font-bold text-foreground mt-4">Pagina nao encontrada</h2>
      <p class="text-muted-foreground mt-2 max-w-md mx-auto">
        A pagina que voce esta procurando nao existe ou foi movida.
      </p>
      <div class="flex items-center justify-center gap-4 mt-8">
        <Button variant="outline" as-child>
          <RouterLink :to="{ name: 'home' }">
            <ArrowLeft class="size-4 mr-2" />
            Voltar
          </RouterLink>
        </Button>
        <Button as-child>
          <RouterLink :to="{ name: 'home' }">
            <Home class="size-4 mr-2" />
            Inicio
          </RouterLink>
        </Button>
      </div>
    </div>
  </div>
</template>
