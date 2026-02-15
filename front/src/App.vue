<script setup lang="ts">
import { RouterView } from 'vue-router'
import { onMounted } from 'vue'

// Inicializa o tema ao carregar o app
onMounted(() => {
  const saved = localStorage.getItem("grapo-theme")
  if (saved === "light") {
    document.documentElement.classList.add("light")
  } else {
    document.documentElement.classList.remove("light")
  }
})
</script>

<template>
  <RouterView />
</template>
