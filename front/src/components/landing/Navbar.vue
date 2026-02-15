<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
</script>

<template>
  <nav class="sticky top-0 z-50 bg-background/90 backdrop-blur-md border-b border-primary/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo -->
        <div class="flex items-center gap-2">
          <img
            src="/icons/icon-96x96.png"
            alt="Grapo"
            class="w-8 h-10"
          />
          <span class="text-xl font-bold tracking-tight text-foreground uppercase">Grapo</span>
        </div>

        <!-- Links + CTA -->
        <div class="flex items-center gap-4">
          <a
            href="#funcionalidades"
            class="hidden sm:block text-sm font-medium text-muted-foreground hover:text-primary transition-colors"
          >
            Funcionalidades
          </a>
          <a
            href="#planos"
            class="hidden sm:block text-sm font-medium text-muted-foreground hover:text-primary transition-colors"
          >
            Planos
          </a>
          <RouterLink
            :to="{ name: 'login' }"
            class="text-sm font-medium text-muted-foreground hover:text-primary transition-colors"
          >
            Entrar
          </RouterLink>
          <Button as-child class="bg-primary hover:bg-primary/90 text-primary-foreground font-semibold shadow-lg shadow-primary/20">
            <RouterLink :to="{ name: 'register' }">
              Começar
            </RouterLink>
          </Button>
        </div>
      </div>
    </div>
  </nav>
</template>
