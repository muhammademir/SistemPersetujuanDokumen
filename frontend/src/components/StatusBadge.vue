<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold tracking-wide border transition-colors',
      badgeStyle.classes
    ]"
  >
    <component :is="badgeStyle.icon" class="w-3.5 h-3.5 shrink-0" />
    <span>{{ statusLabel }}</span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  CheckCircle2,
  Clock,
  Search,
  XCircle,
  AlertTriangle,
  FileText
} from 'lucide-vue-next'
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

const badgeStyle = computed(() => {
  switch (statusValue.value) {
    case 'approved':
      return {
        icon: CheckCircle2,
        classes: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
      }
    case 'submitted':
    case 'pending':
      return {
        icon: Clock,
        classes: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'
      }
    case 'under_review':
      return {
        icon: Search,
        classes: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'
      }
    case 'revision_required':
    case 'revision':
      return {
        icon: AlertTriangle,
        classes: 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20'
      }
    case 'rejected':
      return {
        icon: XCircle,
        classes: 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20'
      }
    case 'draft':
    default:
      return {
        icon: FileText,
        classes: 'bg-gray-100 text-gray-500 border-gray-200'
      }
  }
})
</script>
