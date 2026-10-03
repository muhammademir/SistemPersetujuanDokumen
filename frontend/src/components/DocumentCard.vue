<template>
  <Card
    class="cursor-pointer transition-all hover:shadow-md hover:border-primary-400 border border-surface-200 dark:border-surface-700 flex flex-col justify-between h-full"
    @click="$emit('click', application)"
  >
    <template #header>
      <div class="p-4 pb-0 flex items-center justify-between gap-2">
        <StatusBadge :status="application.status" />
        <span class="text-xs text-surface-500 font-mono">{{ application.code || formattedDate }}</span>
      </div>
    </template>

    <template #content>
      <div class="space-y-3">
        <div class="flex items-center gap-2">
          <Tag :value="application.document_type || 'DOKUMEN'" severity="info" class="text-[10px] font-bold" />
          <Tag
            v-if="application.revision_count > 0"
            :value="`Revisi #${application.revision_count}`"
            severity="warn"
            class="text-[10px] font-bold"
          />
        </div>

        <h3 class="font-brand text-base font-bold text-surface-900 dark:text-surface-0 hover:text-primary-600 transition-colors line-clamp-1">
          {{ application.title }}
        </h3>

        <p class="text-xs text-surface-600 dark:text-surface-400 line-clamp-2 leading-relaxed">
          {{ application.description || 'Tidak ada keterangan tambahan.' }}
        </p>
      </div>
    </template>

    <template #footer>
      <div class="flex items-center justify-between border-t border-surface-200 dark:border-surface-700 pt-3">
        <div class="flex items-center gap-1.5 text-xs text-surface-500">
          <i class="pi pi-paperclip text-xs"></i>
          <span>{{ application.documents_count ?? (application.documents?.length || 0) }} Berkas</span>
          <span v-if="application.applicant?.name" class="truncate max-w-[120px]">
            · {{ application.applicant.name }}
          </span>
        </div>

        <Button
          :label="computedActionLabel"
          icon="pi pi-arrow-right"
          iconPos="right"
          size="small"
          text
          class="p-0 text-xs font-bold"
          @click.stop="$emit('action', application)"
        />
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import type { Application } from '@/types'
import StatusBadge from './StatusBadge.vue'
import { formatDate, getStatusValue } from '@/utils/formatters'

const props = withDefaults(
  defineProps<{
    application: Application
    actionLabel?: string
  }>(),
  {}
)

defineEmits<{
  click: [app: Application]
  action: [app: Application]
}>()

const statusVal = computed(() => getStatusValue(props.application.status))

const computedActionLabel = computed(() => {
  if (props.actionLabel) return props.actionLabel
  if (statusVal.value === 'draft') return 'Lengkapi'
  if (statusVal.value === 'revision_required') return 'Perbaiki'
  return 'Detail'
})

const formattedDate = computed(() => {
  return formatDate(props.application.updated_at || props.application.created_at)
})
</script>
