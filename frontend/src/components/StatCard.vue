<template>
  <Card class="shadow-sm hover:shadow-md transition-shadow border border-surface-200 dark:border-surface-700">
    <template #content>
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">
          {{ label }}
        </span>
        <span class="flex items-center justify-center w-9 h-9 rounded-lg" :class="iconBgClass">
          <Icon :icon="icon" class="text-lg" :class="iconColorClass" />
        </span>
      </div>

      <div class="text-3xl font-bold font-brand tracking-tight" :class="valueClass">
        {{ animatedValue }}
      </div>

      <div v-if="subtitle" class="text-xs text-surface-500 dark:text-surface-400 mt-1.5 flex items-center gap-1">
        <span>{{ subtitle }}</span>
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import Card from 'primevue/card'
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
  'text-surface-900 dark:text-surface-0': props.variant === 'default',
  'text-primary-600 dark:text-primary-400': props.variant === 'primary',
  'text-emerald-600 dark:text-emerald-400': props.variant === 'success',
  'text-amber-600 dark:text-amber-400': props.variant === 'warning',
  'text-red-600 dark:text-red-400': props.variant === 'danger',
}))

const iconColorClass = computed(() => ({
  'text-surface-600 dark:text-surface-300': props.variant === 'default',
  'text-primary-600 dark:text-primary-400': props.variant === 'primary',
  'text-emerald-600 dark:text-emerald-400': props.variant === 'success',
  'text-amber-600 dark:text-amber-400': props.variant === 'warning',
  'text-red-600 dark:text-red-400': props.variant === 'danger',
}))

const iconBgClass = computed(() => ({
  'bg-surface-100 dark:bg-surface-800': props.variant === 'default',
  'bg-primary-50 dark:bg-primary-950/40': props.variant === 'primary',
  'bg-emerald-50 dark:bg-emerald-950/40': props.variant === 'success',
  'bg-amber-50 dark:bg-amber-950/40': props.variant === 'warning',
  'bg-red-50 dark:bg-red-950/40': props.variant === 'danger',
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
