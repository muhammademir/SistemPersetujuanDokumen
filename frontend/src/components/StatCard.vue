<template>
  <Card
    class="relative overflow-hidden transition-all duration-200 hover:shadow-md"
    :class="cardBorderClass"
  >
    <CardContent class="p-4">
      <div class="flex items-start justify-between">
        <div class="flex flex-col gap-1">
          <span class="text-[12px] font-medium text-gray-500 uppercase tracking-wide">
            {{ label }}
          </span>
          <span class="text-2xl font-bold tracking-tight" :class="valueColorClass">
            {{ animatedValue }}
          </span>
          <span v-if="subtitle" class="text-[11px] text-gray-400 mt-0.5">
            {{ subtitle }}
          </span>
        </div>
        <div
          class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
          :class="iconBgClass"
        >
          <Icon :icon="icon" class="text-xl" :class="iconColorClass" />
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Icon } from '@iconify/vue'

const props = withDefaults(
  defineProps<{
    value: number
    label: string
    icon: string
    subtitle?: string
    variant?: 'default' | 'primary' | 'success' | 'warning' | 'danger'
  }>(),
  {
    variant: 'default',
  }
)

const animatedValue = ref(0)

const cardBorderClass = computed(() => ({
  'border-gray-200': props.variant === 'default',
  'border-blue-200': props.variant === 'primary',
  'border-emerald-200': props.variant === 'success',
  'border-amber-200': props.variant === 'warning',
  'border-red-200': props.variant === 'danger',
}))

const valueColorClass = computed(() => ({
  'text-gray-900': props.variant === 'default',
  'text-blue-600': props.variant === 'primary',
  'text-emerald-600': props.variant === 'success',
  'text-amber-600': props.variant === 'warning',
  'text-red-600': props.variant === 'danger',
}))

const iconColorClass = computed(() => ({
  'text-gray-500': props.variant === 'default',
  'text-blue-500': props.variant === 'primary',
  'text-emerald-500': props.variant === 'success',
  'text-amber-500': props.variant === 'warning',
  'text-red-500': props.variant === 'danger',
}))

const iconBgClass = computed(() => ({
  'bg-gray-100': props.variant === 'default',
  'bg-blue-50': props.variant === 'primary',
  'bg-emerald-50': props.variant === 'success',
  'bg-amber-50': props.variant === 'warning',
  'bg-red-50': props.variant === 'danger',
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
watch(
  () => props.value,
  (v) => animateValue(v)
)
</script>
