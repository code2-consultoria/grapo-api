<script setup lang="ts">
import {
  BarChart3,
  FileText,
  Bell,
  QrCode,
  CreditCard,
  Users,
} from 'lucide-vue-next'

const features = [
  {
    icon: BarChart3,
    title: 'Dashboard Financeiro',
    description: 'Acompanhe ROI, receita mensal e taxas de utilização em tempo real.',
  },
  {
    icon: FileText,
    title: 'Gestão de Contratos',
    description: 'Assinatura digital integrada e renovação automática de termos.',
  },
  {
    icon: Bell,
    title: 'Alertas Inteligentes',
    description: 'Notificações de contratos vencendo, pagamentos atrasados e manutenções.',
  },
  {
    icon: QrCode,
    title: 'Rastreio via QR Code',
    description: 'Etiquete seus ativos e tenha controle total de entrada e saída.',
  },
  {
    icon: CreditCard,
    title: 'Cobrança Automática',
    description: 'Integração com Stripe. Seus clientes pagam online, você recebe sem dor de cabeça.',
  },
  {
    icon: Users,
    title: 'Multi-usuário',
    description: 'Controle níveis de acesso para sua equipe de vendas e técnica.',
  },
]
</script>

<template>
  <section id="funcionalidades" class="py-20 px-4">
    <div class="max-w-7xl mx-auto">
      <!-- Título da seção -->
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-4">
          Tudo o que você precisa para gerir
        </h2>
        <p class="text-muted-foreground">
          Uma plataforma completa, pensada na eficiência operacional.
        </p>
      </div>

      <!-- Grid de funcionalidades -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="feature in features"
          :key="feature.title"
          class="flex gap-4 items-start"
        >
          <div class="bg-primary/10 p-3 rounded-lg shrink-0">
            <component :is="feature.icon" class="w-6 h-6 text-primary" />
          </div>
          <div>
            <h4 class="text-lg font-bold text-foreground mb-2">
              {{ feature.title }}
            </h4>
            <p class="text-sm text-muted-foreground leading-relaxed">
              {{ feature.description }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
