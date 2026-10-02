<template>
  <span
    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-sm text-xs font-bold uppercase tracking-wide border transition-all"
    :class="badgeClasses"
  >
    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" :class="dotClass"></span>
    <span>{{ statusLabel }}</span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { ApplicationStatus, ApplicationStatusObj } from '@/types'

const props = defineProps<{
  status: ApplicationStatus | ApplicationStatusObj | string
}>()

const statusValue = computed<string>(() => {
  if (!props.status) return 'draft'
  if (typeof props.status === 'object') {
    return props.status.value || 'draft'
  }
  return props.status
})

const statusLabel = computed<string>(() => {
  if (typeof props.status === 'object' && props.status.label) {
    return props.status.label
  }
  const map: Record<string, string> = {
    draft: 'Draft',
    submitted: 'Menunggu Verifikasi',
    under_review: 'Sedang Ditinjau',
    revision_required: 'Perlu Revisi',
    approved: 'Disetujui',
    rejected: 'Ditolak',
    pending: 'Menunggu Verifikasi',
    revision: 'Perlu Revisi',
  }
  return map[statusValue.value] ?? statusValue.value
})

const badgeClasses = computed(() => {
  const val = statusValue.value
  switch (val) {
    case 'draft':
      return 'bg-stone/10 border-stone/30 text-stone'
    case 'submitted':
    case 'pending':
      return 'bg-warning/10 border-warning/30 text-warning'
    case 'under_review':
      return 'bg-info/10 border-info/30 text-info'
    case 'revision_required':
    case 'revision':
      return 'bg-orange-500/10 border-orange-500/30 text-orange-600 dark:text-orange-400'
    case 'approved':
      return 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700 dark:text-emerald-400'
    case 'rejected':
      return 'bg-error/10 border-error/30 text-error'
    default:
      return 'bg-surface-soft border-hairline text-mute'
  }
})

const dotClass = computed(() => {
  const val = statusValue.value
  switch (val) {
    case 'draft':
      return 'bg-stone'
    case 'submitted':
    case 'pending':
      return 'bg-warning animate-pulse'
    case 'under_review':
      return 'bg-info animate-pulse'
    case 'revision_required':
    case 'revision':
      return 'bg-orange-500'
    case 'approved':
      return 'bg-emerald-500'
    case 'rejected':
      return 'bg-error'
    default:
      return 'bg-mute'
  }
})
</script>
