<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card'
import { Package, FileText, DollarSign } from 'lucide-vue-next'

const problems = [
  {
    icon: Package,
    title: 'Equipamentos Ociosos',
    description: 'Máquinas paradas geram apenas custos de manutenção e depreciação. Coloque-as para trabalhar.',
  },
  {
    icon: FileText,
    title: 'Contratos Manuais',
    description: 'Erros em planilhas e contratos físicos causam prejuízos jurídicos e financeiros constantes.',
  },
  {
    icon: DollarSign,
    title: 'Inadimplência',
    description: 'A falta de um sistema de cobrança automatizado faz com que você perca o controle do fluxo de caixa.',
  },
]
</script>

<template>
  <section class="py-20 px-4 bg-card/50">
    <div class="max-w-7xl mx-auto">
      <!-- Título da seção -->
      <div class="text-center mb-12">
        <h2 class="text-2xl md:text-3xl font-bold text-foreground mb-4">
          Por que seus ativos estão perdendo valor?
        </h2>
        <div class="h-1 w-20 bg-primary mx-auto rounded"></div>
      </div>

      <!-- Cards de problemas -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card
          v-for="problem in problems"
          :key="problem.title"
          class="bg-card border-border/5 hover:border-primary/30 transition-all group"
        >
          <CardContent class="p-6">
            <component
              :is="problem.icon"
              class="w-10 h-10 text-primary mb-4 group-hover:scale-110 transition-transform"
            />
            <h3 class="text-xl font-bold text-foreground mb-2">
              {{ problem.title }}
            </h3>
            <p class="text-muted-foreground leading-relaxed">
              {{ problem.description }}
            </p>
          </CardContent>
        </Card>
      </div>
    </div>
  </section>
</template>
