<template>
  <span
    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-sm text-xs font-bold uppercase tracking-wide"
    :class="badgeClasses"
  >
    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" :class="dotClass"></span>
    <span>{{ label }}</span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { DocumentStatus } from '@/types'

const props = defineProps<{
  status: DocumentStatus
}>()

const label = computed(() => {
  const map: Record<DocumentStatus, string> = {
    draft: 'Draft',
    pending: 'Menunggu',
    approved: 'Disetujui',
    revision: 'Revisi',
    rejected: 'Ditolak',
  }
  return map[props.status] ?? props.status
})

const badgeClasses = computed(() => ({
  'bg-surface-soft text-stone': props.status === 'draft',
  'bg-accent-yellow-pale text-warning': props.status === 'pending',
  'bg-primary/10 text-success-deep': props.status === 'approved',
  'bg-warning/10 text-warning': props.status === 'revision',
  'bg-error/10 text-error': props.status === 'rejected',
}))

const dotClass = computed(() => ({
  'bg-stone': props.status === 'draft',
  'bg-warning': props.status === 'pending',
  'bg-primary': props.status === 'approved',
  'bg-warning-bright': props.status === 'revision',
  'bg-error': props.status === 'rejected',
}))
</script>
