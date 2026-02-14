<script setup lang="ts">
import type { HTMLAttributes, Component } from "vue"
import { cn } from "@/lib/utils"
import { Card, CardContent } from "@/components/ui/card"

interface Props {
  title: string
  value: string | number
  description?: string
  icon?: Component
  trend?: "up" | "down" | "neutral"
  trendValue?: string
  class?: HTMLAttributes["class"]
}

const props = defineProps<Props>()
</script>

<template>
  <Card :class="cn(props.class)">
    <CardContent class="p-6">
      <div class="flex items-start justify-between">
        <div class="space-y-1">
          <p class="text-sm font-medium text-muted-foreground">{{ title }}</p>
          <p class="text-2xl font-bold">{{ value }}</p>
          <p v-if="description" class="text-xs text-muted-foreground">
            {{ description }}
          </p>
        </div>
        <div
          v-if="icon"
          class="rounded-lg bg-primary/10 p-2.5 text-primary"
        >
          <component :is="icon" class="size-5" />
        </div>
      </div>
      <div class="mt-3 flex items-center justify-between">
        <div v-if="trendValue" class="flex items-center gap-1 text-xs">
          <span
            :class="[
              trend === 'up' && 'text-green-600',
              trend === 'down' && 'text-red-600',
              trend === 'neutral' && 'text-muted-foreground',
            ]"
          >
            {{ trendValue }}
          </span>
          <span class="text-muted-foreground">vs. mes anterior</span>
        </div>
        <slot name="action" />
      </div>
    </CardContent>
  </Card>
</template>
