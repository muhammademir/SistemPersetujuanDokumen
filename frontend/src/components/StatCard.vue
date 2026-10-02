<template>
  <div
    class="relative overflow-hidden bg-canvas border border-hairline rounded-sm p-6 transition-colors hover:border-primary"
    :class="variantClasses"
  >
    <!-- Corner square -->
    <div class="absolute top-0 left-0 w-3 h-3 bg-primary"></div>

    <div class="flex items-center gap-2 mb-3">
      <span class="flex items-center justify-center w-9 h-9 rounded-sm" :class="iconBgClass">
        <Icon :icon="icon" class="text-lg" />
      </span>
      <span class="text-xs font-bold uppercase tracking-wide text-mute">{{ label }}</span>
    </div>

    <div class="font-brand text-4xl font-bold leading-tight" :class="valueClass">
      {{ animatedValue }}
    </div>

    <div v-if="subtitle" class="text-xs text-mute mt-1">{{ subtitle }}</div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
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

const variantClasses = {
  default: '',
  primary: '',
  success: '',
  warning: '',
  danger: '',
}

import { computed } from 'vue'

const valueClass = computed(() => ({
  'text-ink': props.variant === 'default',
  'text-primary': props.variant === 'primary',
  'text-success-deep': props.variant === 'success',
  'text-warning': props.variant === 'warning',
  'text-error': props.variant === 'danger',
}))

const iconBgClass = computed(() => ({
  'bg-surface-soft': props.variant === 'default',
  'bg-primary/10': props.variant === 'primary',
  'bg-success-deep/10': props.variant === 'success',
  'bg-warning/10': props.variant === 'warning',
  'bg-error/10': props.variant === 'danger',
}))

function animateValue(target: number) {
  const duration = 800
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
