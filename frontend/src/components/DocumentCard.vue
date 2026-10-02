<template>
  <div
    class="relative overflow-hidden bg-canvas border border-hairline rounded-sm p-6 cursor-pointer transition-colors hover:border-primary"
    @click="$emit('click', document)"
  >
    <!-- Corner square -->
    <div class="absolute top-0 left-0 w-3 h-3 bg-primary"></div>

    <div class="flex items-center justify-between mb-3">
      <StatusBadge :status="document.status" />
      <span class="text-xs text-mute">{{ formattedDate }}</span>
    </div>

    <h3 class="font-brand text-base font-bold leading-snug text-ink mb-2">{{ document.title }}</h3>

    <p class="text-sm leading-relaxed text-body line-clamp-2 mb-4">{{ document.description }}</p>

    <div class="flex items-center justify-between border-t border-hairline pt-3">
      <div class="flex items-center gap-2">
        <Icon icon="mdi:paperclip" class="text-mute text-sm" />
        <span class="text-xs text-mute">{{ document.file_name }}</span>
      </div>
      <button
        class="bg-transparent border-none text-primary text-sm font-bold cursor-pointer hover:text-primary-dark transition-colors"
        @click.stop="$emit('action', document)"
      >
        {{ actionLabel }} →
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import type { Document } from '@/types'
import StatusBadge from './StatusBadge.vue'

const props = withDefaults(defineProps<{
  document: Document
  actionLabel?: string
}>(), {
  actionLabel: 'Lihat Detail',
})

defineEmits<{
  click: [doc: Document]
  action: [doc: Document]
}>()

const formattedDate = computed(() => {
  return new Date(props.document.updated_at).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
  })
})
</script>
