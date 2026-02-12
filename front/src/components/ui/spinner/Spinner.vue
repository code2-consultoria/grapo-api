<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import type { SpinnerVariants } from "."
import { cn } from "@/lib/utils"
import { spinnerVariants } from "."
import { Loader2 } from "lucide-vue-next"

interface Props {
  size?: SpinnerVariants["size"]
  class?: HTMLAttributes["class"]
}

const props = withDefaults(defineProps<Props>(), {
  size: "default",
})
</script>

<template>
  <Loader2 data-slot="spinner" :class="cn(spinnerVariants({ size }), props.class)" />
</template>
