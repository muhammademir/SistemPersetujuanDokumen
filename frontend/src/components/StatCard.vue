<template>
  <Card class="hover:shadow-md transition-shadow border">
    <CardContent class="p-5">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
          {{ label }}
        </span>
        <span class="flex items-center justify-center w-9 h-9 rounded-lg" :class="iconBgClass">
          <Icon :icon="icon" class="text-lg" :class="iconColorClass" />
        </span>
      </div>

      <div class="text-3xl font-bold tracking-tight" :class="valueClass">
        {{ animatedValue }}
      </div>

      <div v-if="subtitle" class="text-xs text-muted-foreground mt-1.5 flex items-center gap-1">
        <span>{{ subtitle }}</span>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Icon } from '@iconify/vue'

const props = withDefaults(defineProps<{
  value: number
  label: string
  icon: string
  subtitle?: string
  variant?: 'default' | 'primary' | 'success' | 'warning' | 'danger'
}>(), {
  variant: 'default',
})

const animatedValue = ref(0)

const valueClass = computed(() => ({
  'text-foreground': props.variant === 'default',
  'text-primary': props.variant === 'primary',
  'text-emerald-600 dark:text-emerald-400': props.variant === 'success',
  'text-amber-600 dark:text-amber-400': props.variant === 'warning',
  'text-red-600 dark:text-red-400': props.variant === 'danger',
}))

const iconColorClass = computed(() => ({
  'text-muted-foreground': props.variant === 'default',
  'text-primary': props.variant === 'primary',
  'text-emerald-600 dark:text-emerald-400': props.variant === 'success',
  'text-amber-600 dark:text-amber-400': props.variant === 'warning',
  'text-red-600 dark:text-red-400': props.variant === 'danger',
}))

const iconBgClass = computed(() => ({
  'bg-muted': props.variant === 'default',
  'bg-primary/10': props.variant === 'primary',
  'bg-emerald-500/10': props.variant === 'success',
  'bg-amber-500/10': props.variant === 'warning',
  'bg-red-500/10': props.variant === 'danger',
}))

function animateValue(target: number) {
  const duration = 600
  const start = animatedValue.value
  const startTime = performance.now()
  function tick(now: number) {
    const elapsed = now - startTime
    const progress = Math.min(elapsed / duration, 1)
    const eased = 1 - Math.pow(1 - progress, 3)
    animatedValue.value = Math.round(start + (target - start) * eased)
    if (progress < 1) requestAnimationFrame(tick)
  }
  requestAnimationFrame(tick)
}

onMounted(() => animateValue(props.value))
watch(() => props.value, (v) => animateValue(v))
</script>
