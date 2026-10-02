<template>
  <div
    class="relative overflow-hidden bg-canvas border border-hairline rounded-sm p-6 cursor-pointer transition-all hover:border-primary hover:shadow-sm group flex flex-col justify-between"
    @click="$emit('click', application)"
  >
    <!-- Corner square -->
    <div class="absolute top-0 left-0 w-3 h-3 bg-primary"></div>

    <div>
      <div class="flex items-center justify-between gap-2 mb-3">
        <StatusBadge :status="application.status" />
        <span class="text-xs text-mute font-mono">{{ application.code || formattedDate }}</span>
      </div>

      <div class="flex items-center gap-2 mb-2">
        <span class="px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase bg-primary/10 text-primary border border-primary/20">
          {{ application.document_type || 'DOKUMEN' }}
        </span>
        <span v-if="application.revision_count > 0" class="text-[10px] font-bold text-warning">
          (Revisi ke-{{ application.revision_count }})
        </span>
      </div>

      <h3 class="font-brand text-base font-bold leading-snug text-ink mb-2 group-hover:text-primary transition-colors">
        {{ application.title }}
      </h3>

      <p class="text-sm leading-relaxed text-body line-clamp-2 mb-4">
        {{ application.description || 'Tidak ada keterangan tambahan.' }}
      </p>
    </div>

    <div class="flex items-center justify-between border-t border-hairline pt-3 mt-2">
      <div class="flex items-center gap-2">
        <Icon icon="mdi:paperclip" class="text-mute text-sm" />
        <span class="text-xs text-mute">
          {{ application.documents_count ?? (application.documents?.length || 0) }} Berkas
        </span>
        <span v-if="application.applicant?.name" class="text-xs text-mute ml-1">
          · {{ application.applicant.name }}
        </span>
      </div>
      <button
        class="bg-transparent border-none text-primary text-xs font-bold cursor-pointer hover:text-primary-dark transition-colors"
        @click.stop="$emit('action', application)"
      >
        {{ computedActionLabel }} →
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import type { Application } from '@/types'
import StatusBadge from './StatusBadge.vue'

const props = withDefaults(defineProps<{
  application: Application
  actionLabel?: string
}>(), {})

defineEmits<{
  click: [app: Application]
  action: [app: Application]
}>()

const statusVal = computed(() => {
  if (typeof props.application.status === 'object') {
    return props.application.status.value
  }
  return props.application.status
})

const computedActionLabel = computed(() => {
  if (props.actionLabel) return props.actionLabel
  if (statusVal.value === 'draft') return 'Lengkapi & Ajukan'
  if (statusVal.value === 'revision_required') return 'Perbaiki'
  return 'Lihat Detail'
})

const formattedDate = computed(() => {
  const d = props.application.updated_at || props.application.created_at
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
  })
})
</script>
