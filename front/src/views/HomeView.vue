<script setup lang="ts">
import {
  Navbar,
  Hero,
  Problems,
  Features,
  Pricing,
  Footer,
} from '@/components/landing'
</script>

<template>
  <div class="min-h-screen bg-background">
    <Navbar />
    <main>
      <Hero />
      <Problems />
      <Features />
      <Pricing />
    </main>
    <Footer />
  </div>
</template>
