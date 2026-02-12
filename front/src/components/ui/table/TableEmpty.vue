<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"
import { Inbox } from "lucide-vue-next"

interface Props {
  colspan?: number
  message?: string
  class?: HTMLAttributes["class"]
}

const props = withDefaults(defineProps<Props>(), {
  colspan: 1,
  message: "Nenhum registro encontrado",
})
</script>

<template>
  <tr>
    <td
      :colspan="colspan"
      :class="cn('py-12 text-center', props.class)"
    >
      <div class="flex flex-col items-center gap-2 text-muted-foreground">
        <Inbox class="size-8" />
        <span>{{ message }}</span>
      </div>
    </td>
  </tr>
</template>
