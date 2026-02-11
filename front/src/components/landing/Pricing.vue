<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card'
import { Check } from 'lucide-vue-next'

const plans = [
  {
    name: 'Trimestral',
    period: '3 meses',
    price: 75,
    pricePerMonth: '25,00',
    featured: false,
    features: [
      'Ativos ilimitados',
      'Contratos ilimitados',
      'Cobranças via Stripe',
      'Dashboard completo',
      'Alertas por e-mail',
      'Suporte por e-mail',
    ],
  },
  {
    name: 'Anual',
    period: '12 meses',
    price: 250,
    pricePerMonth: '20,83',
    featured: true,
    badge: 'Mais Popular',
    savings: 'Economize 17%',
    features: [
      'Ativos ilimitados',
      'Contratos ilimitados',
      'Cobranças via Stripe',
      'Dashboard completo',
      'Alertas por e-mail',
      'Suporte por e-mail',
    ],
  },
  {
    name: 'Semestral',
    period: '6 meses',
    price: 140,
    pricePerMonth: '23,33',
    featured: false,
    features: [
      'Ativos ilimitados',
      'Contratos ilimitados',
      'Cobranças via Stripe',
      'Dashboard completo',
      'Alertas por e-mail',
      'Suporte por e-mail',
    ],
  },
]
</script>

<template>
  <section id="planos" class="py-20 px-4 bg-card/30">
    <div class="max-w-7xl mx-auto">
      <!-- Título da seção -->
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-4">
          Escolha o plano ideal
        </h2>
        <p class="text-muted-foreground">
          Sem taxas escondidas. Cancele quando quiser. 7 dias grátis para testar.
        </p>
      </div>

      <!-- Cards de planos -->
      <div class="flex flex-col md:flex-row gap-8 max-w-5xl mx-auto items-center">
        <Card
          v-for="plan in plans"
          :key="plan.name"
          :class="[
            'w-full transition-all',
            plan.featured
              ? 'border-2 border-primary shadow-2xl shadow-primary/20 scale-105 z-10'
              : 'border-border/5'
          ]"
        >
          <!-- Badge para plano destacado -->
          <div
            v-if="plan.featured"
            class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary text-primary-foreground px-4 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
          >
            {{ plan.badge }}
          </div>

          <CardHeader class="text-center pb-2">
            <CardTitle class="text-xl font-bold">{{ plan.name }}</CardTitle>
            <p class="text-sm text-muted-foreground">{{ plan.period }}</p>
          </CardHeader>

          <CardContent class="text-center">
            <!-- Preço -->
            <div class="mb-2">
              <span class="text-4xl font-bold text-foreground">R$ {{ plan.price }}</span>
              <span class="text-muted-foreground text-sm">/total</span>
            </div>
            <p class="text-sm text-primary font-medium mb-6">
              Equivalente a R$ {{ plan.pricePerMonth }}/mês
            </p>

            <!-- Economia -->
            <p v-if="plan.savings" class="text-xs text-primary font-bold mb-4">
              {{ plan.savings }}
            </p>

            <!-- Features -->
            <ul class="space-y-3 text-left mb-6">
              <li
                v-for="feature in plan.features"
                :key="feature"
                class="flex items-center gap-2 text-sm"
                :class="plan.featured ? 'text-foreground' : 'text-muted-foreground'"
              >
                <Check class="w-4 h-4 text-primary shrink-0" />
                {{ feature }}
              </li>
            </ul>
          </CardContent>

          <CardFooter>
            <Button
              :variant="plan.featured ? 'default' : 'outline'"
              class="w-full"
              :class="plan.featured
                ? 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/20'
                : 'border-primary text-primary hover:bg-primary/5'"
            >
              {{ plan.featured ? 'Começar Agora' : 'Selecionar' }}
            </Button>
          </CardFooter>
        </Card>
      </div>
    </div>
  </section>
</template>
