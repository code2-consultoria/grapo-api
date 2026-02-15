<script setup lang="ts">
import { RouterView } from "vue-router"
import { ToastContainer } from "@/components/ui/toast"
</script>

<template>
  <div class="min-h-screen bg-background flex">
    <!-- Toast notifications -->
    <ToastContainer />
    <!-- Lado esquerdo - branding -->
    <div class="hidden lg:flex lg:w-1/2 bg-card items-center justify-center p-12">
      <div class="text-center">
        <div class="flex items-center justify-center gap-3 mb-8">
          <img
            src="/icons/icon-96x96.png"
            alt="Grapo"
            class="w-12 h-16"
          />
          <span class="text-3xl font-bold text-foreground uppercase">Grapo</span>
        </div>
        <h1 class="text-2xl font-semibold text-foreground mb-4">
          Transforme equipamentos em renda
        </h1>
        <p class="text-muted-foreground max-w-md">
          Gerencie seus ativos, contratos e locatarios em um so lugar.
        </p>
      </div>
    </div>

    <!-- Lado direito - formulario -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
      <div class="w-full max-w-md">
        <RouterView />
      </div>
    </div>
  </div>
</template>
