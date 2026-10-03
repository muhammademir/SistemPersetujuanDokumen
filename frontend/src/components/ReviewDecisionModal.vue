<template>
  <Dialog
    :visible="modelValue"
    @update:visible="emit('update:modelValue', $event)"
    modal
    header="Verifikasi & Keputusan Permohonan"
    :style="{ width: '90vw', maxWidth: '600px' }"
  >
    <div v-if="application" class="space-y-5 pt-2">
      <!-- Target Summary Box -->
      <div class="p-4 bg-surface-50 dark:bg-surface-800/60 rounded-lg border border-surface-200 dark:border-surface-700 flex flex-col gap-2 text-xs">
        <div class="flex items-center justify-between gap-2">
          <span class="font-mono text-surface-500 font-bold">{{ application.code }}</span>
          <Tag :value="application.document_type" severity="info" class="text-xs uppercase font-bold" />
        </div>
        <h3 class="text-sm font-bold text-surface-900 dark:text-surface-0">{{ application.title }}</h3>
        <p class="text-surface-600 dark:text-surface-400 text-xs line-clamp-2">
          {{ application.description || 'Tidak ada catatan tambahan.' }}
        </p>
        <div v-if="application.applicant" class="text-[11px] text-surface-500 mt-1">
          Pemohon: <span class="font-semibold text-surface-800 dark:text-surface-200">{{ application.applicant.name }}</span> ({{ application.applicant.email }})
        </div>
      </div>

      <!-- Decision Options -->
      <div class="space-y-2">
        <label class="block text-xs font-bold uppercase tracking-wider text-surface-700 dark:text-surface-300">
          Pilih Keputusan Penilaian *
        </label>
        <div class="grid grid-cols-3 gap-3">
          <div
            v-for="opt in decisionOptions"
            :key="opt.value"
            @click="decision = opt.value"
            class="p-3.5 flex flex-col items-center gap-2 rounded-lg border-2 cursor-pointer transition-all text-center"
            :class="decision === opt.value ? opt.activeClass : 'border-surface-200 dark:border-surface-700 hover:border-surface-400'"
          >
            <i :class="opt.icon" class="text-2xl" :style="{ color: opt.iconColor }"></i>
            <span class="text-xs font-bold">{{ opt.label }}</span>
          </div>
        </div>
      </div>

      <!-- Review Notes Input -->
      <div class="space-y-1.5">
        <label class="block text-xs font-bold uppercase tracking-wider text-surface-700 dark:text-surface-300">
          Catatan Penilaian {{ isRevisionRequired ? '(Wajib Diisi)' : '(Opsional)' }}
        </label>
        <Textarea
          v-model="note"
          rows="4"
          class="w-full text-xs"
          placeholder="Berikan alasan atau instruksi revisi yang jelas untuk pemohon..."
          autoResize
        />
      </div>
    </div>

    <template #footer>
      <div class="flex items-center justify-end gap-2 pt-2">
        <Button label="Batal" severity="secondary" text @click="handleClose" />
        <Button
          label="Kirim Keputusan"
          icon="pi pi-check"
          severity="primary"
          :loading="loading"
          :disabled="!isValid || loading"
          @click="handleSubmit"
        />
      </div>
    </template>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Textarea from 'primevue/textarea'
import type { Application } from '@/types'

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    application: Application | null
    loading?: boolean
  }>(),
  {
    loading: false,
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  submit: [payload: { decision: string; note: string }]
}>()

const decision = ref<string>('approved')
const note = ref<string>('')

const isRevisionRequired = computed(() => decision.value === 'revision_required')
const isValid = computed(() => {
  if (!decision.value) return false
  if (isRevisionRequired.value && !note.value.trim()) return false
  return true
})

const decisionOptions = [
  {
    value: 'approved',
    label: 'Disetujui',
    icon: 'pi pi-check-circle',
    iconColor: '#10b981',
    activeClass: 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300',
  },
  {
    value: 'revision_required',
    label: 'Perlu Revisi',
    icon: 'pi pi-pencil',
    iconColor: '#f59e0b',
    activeClass: 'border-amber-500 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300',
  },
  {
    value: 'rejected',
    label: 'Ditolak',
    icon: 'pi pi-times-circle',
    iconColor: '#ef4444',
    activeClass: 'border-red-500 bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300',
  },
]

watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) {
      decision.value = 'approved'
      note.value = ''
    }
  }
)

function handleClose() {
  emit('update:modelValue', false)
}

function handleSubmit() {
  if (!isValid.value) return
  emit('submit', {
    decision: decision.value,
    note: note.value.trim(),
  })
}
</script>
