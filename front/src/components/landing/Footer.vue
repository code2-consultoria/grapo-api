<script setup lang="ts">
import { Wallet } from 'lucide-vue-next'

const platformLinks = [
  { label: 'Funcionalidades', href: '#funcionalidades' },
  { label: 'Planos e Preços', href: '#planos' },
  { label: 'Segurança', href: '#' },
  { label: 'API Docs', href: '#' },
]

const companyLinks = [
  { label: 'Sobre nós', href: '#' },
  { label: 'Contato', href: '#' },
  { label: 'Privacidade', href: '#' },
  { label: 'Termos de Uso', href: '#' },
]
</script>

<template>
  <footer class="bg-background border-t border-primary/10 pt-20 pb-10 px-4">
    <div class="max-w-7xl mx-auto">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
        <!-- Logo e descrição -->
        <div class="col-span-1 md:col-span-2">
          <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-8 bg-primary rounded flex items-center justify-center">
              <Wallet class="w-5 h-5 text-primary-foreground" />
            </div>
            <span class="text-2xl font-bold tracking-tight text-foreground uppercase">Grapo</span>
          </div>
          <p class="text-muted-foreground max-w-sm leading-relaxed">
            Ajudamos locadoras e empresas a profissionalizarem a gestão de seus ativos com tecnologia de ponta.
          </p>
        </div>

        <!-- Links Plataforma -->
        <div>
          <h5 class="text-primary font-bold mb-6">Plataforma</h5>
          <ul class="space-y-4 text-sm text-muted-foreground">
            <li v-for="link in platformLinks" :key="link.label">
              <a :href="link.href" class="hover:text-foreground transition-colors">
                {{ link.label }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Links Empresa -->
        <div>
          <h5 class="text-primary font-bold mb-6">Empresa</h5>
          <ul class="space-y-4 text-sm text-muted-foreground">
            <li v-for="link in companyLinks" :key="link.label">
              <a :href="link.href" class="hover:text-foreground transition-colors">
                {{ link.label }}
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Copyright -->
      <div class="pt-8 border-t border-border/5 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-muted-foreground">
        <p>&copy; 2025 Grapo. Todos os direitos reservados.</p>
        <div class="flex gap-4">
          <span>Feito com dedicação no Brasil</span>
        </div>
      </div>
    </div>
  </footer>
</template>
