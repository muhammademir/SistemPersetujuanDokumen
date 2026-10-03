<template>
  <Tag :severity="severity" :value="statusLabel" class="uppercase text-[11px] font-bold tracking-wider px-2.5 py-0.5" rounded>
    <template #icon>
      <i :class="iconClass" class="mr-1 text-[10px]"></i>
    </template>
  </Tag>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Tag from 'primevue/tag'
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

const severity = computed(() => {
  const val = statusValue.value
  switch (val) {
    case 'approved':
      return 'success'
    case 'submitted':
    case 'pending':
    case 'revision_required':
    case 'revision':
      return 'warn'
    case 'under_review':
      return 'info'
    case 'rejected':
      return 'danger'
    case 'draft':
    default:
      return 'secondary'
  }
})

const iconClass = computed(() => {
  const val = statusValue.value
  switch (val) {
    case 'approved':
      return 'pi pi-check-circle'
    case 'submitted':
    case 'pending':
      return 'pi pi-clock'
    case 'under_review':
      return 'pi pi-search'
    case 'revision_required':
    case 'revision':
      return 'pi pi-pencil'
    case 'rejected':
      return 'pi pi-times-circle'
    case 'draft':
    default:
      return 'pi pi-file-edit'
  }
})
</script>
