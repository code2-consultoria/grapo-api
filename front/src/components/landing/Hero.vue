<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Play } from 'lucide-vue-next'
</script>

<template>
  <header class="relative pt-16 pb-20 px-4 overflow-hidden">
    <!-- Background decorativo -->
    <div class="absolute inset-0 z-0">
      <div class="absolute top-[-10%] right-[-10%] w-[300px] h-[300px] bg-primary/10 rounded-full blur-[100px]"></div>
      <div class="absolute bottom-[-10%] left-[-10%] w-[250px] h-[250px] bg-primary/5 rounded-full blur-[80px]"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto text-center">
      <!-- Badge -->
      <span class="inline-block py-1 px-3 rounded-full bg-primary/10 text-primary text-xs font-bold tracking-widest uppercase mb-6">
        Plataforma de Gestão de Ativos
      </span>

      <!-- Título -->
      <h1 class="text-4xl md:text-6xl font-bold text-foreground mb-6 leading-tight">
        Transforme seus equipamentos
        <span class="text-primary">parados em renda recorrente</span>
      </h1>

      <!-- Subtítulo -->
      <p class="text-muted-foreground text-lg mb-10 max-w-2xl mx-auto leading-relaxed">
        A Grapo automatiza todo o ciclo de locação dos seus bens, do contrato ao faturamento, para você escalar seu negócio com segurança.
      </p>

      <!-- CTAs -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <Button
          size="lg"
          class="bg-primary text-primary-foreground px-8 py-6 text-lg font-bold shadow-xl shadow-primary/30 hover:bg-primary/90"
        >
          Criar minha conta grátis
        </Button>
        <Button
          variant="ghost"
          size="lg"
          class="text-primary font-semibold hover:bg-primary/10"
        >
          <Play class="w-5 h-5 mr-2" />
          Ver demonstração
        </Button>
      </div>
    </div>

    <!-- Imagem do Dashboard (placeholder) -->
    <div class="mt-16 relative mx-auto max-w-xs md:max-w-4xl">
      <div class="rounded-xl overflow-hidden shadow-2xl shadow-primary/5 border border-primary/20 bg-card">
        <div class="aspect-video flex items-center justify-center text-muted-foreground">
          <p class="text-sm">Imagem do Dashboard</p>
        </div>
      </div>
    </div>
  </header>
</template>
