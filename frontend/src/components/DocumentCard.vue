<template>
  <Card
    class="cursor-pointer transition-all hover:shadow-md hover:border-[#3b49f5]/30 flex flex-col justify-between h-full group border-gray-200"
    @click="$emit('click', application)"
  >
    <CardHeader class="p-4 pb-0 flex flex-row items-center justify-between gap-2 space-y-0">
      <StatusBadge :status="application.status" />
      <span class="text-[11px] text-gray-400 font-mono">
        {{ application.code || formattedDate }}
      </span>
    </CardHeader>

    <CardContent class="p-4 pt-3 space-y-3">
      <div class="flex items-center gap-1.5 flex-wrap">
        <Badge
          variant="outline"
          class="text-[10px] font-semibold uppercase text-gray-500 border-gray-300"
        >
          {{ application.document_type || 'DOKUMEN' }}
        </Badge>
        <Badge
          v-if="application.revision_count > 0"
          variant="secondary"
          class="text-[10px] font-semibold text-amber-600 bg-amber-50 border border-amber-200"
        >
          Revisi #{{ application.revision_count }}
        </Badge>
      </div>

      <h3
        class="text-[14px] font-bold text-gray-900 group-hover:text-[#3b49f5] transition-colors line-clamp-1"
      >
        {{ application.title }}
      </h3>

      <p class="text-[12px] text-gray-400 line-clamp-2 leading-relaxed">
        {{ application.description || 'Tidak ada keterangan tambahan.' }}
      </p>
    </CardContent>

    <CardFooter class="p-4 pt-0 flex items-center justify-between border-t border-gray-100 mt-auto pt-3">
      <div class="flex items-center gap-1.5 text-[11px] text-gray-400">
        <Paperclip class="w-3.5 h-3.5" />
        <span>{{ application.documents_count ?? (application.documents?.length || 0) }} Berkas</span>
        <span v-if="application.applicant?.name" class="truncate max-w-[120px]">
          · {{ application.applicant.name }}
        </span>
      </div>

      <Button
        variant="ghost"
        size="sm"
        class="h-7 px-2 text-[12px] font-semibold gap-1 text-[#3b49f5] hover:text-[#2f3ce0] hover:bg-[#eef0ff]"
        @click.stop="$emit('action', application)"
      >
        <span>{{ computedActionLabel }}</span>
        <ArrowRight class="w-3.5 h-3.5" />
      </Button>
    </CardFooter>
  </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardHeader, CardContent, CardFooter } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Paperclip, ArrowRight } from 'lucide-vue-next'
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
