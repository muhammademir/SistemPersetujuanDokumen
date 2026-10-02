<template>
  <Teleport to="body">
    <transition name="modal">
      <div
        v-if="modelValue && application"
        class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 sm:p-6 z-[100] backdrop-blur-xs"
        @click.self="handleClose"
      >
        <div class="bg-canvas rounded-sm w-full max-w-[580px] max-h-[92vh] overflow-y-auto border border-hairline shadow-2xl animate-fade-in-up">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-hairline bg-surface-soft">
            <div class="flex items-center gap-2.5">
              <div class="w-3 h-3 bg-primary"></div>
              <h2 class="text-base font-bold text-ink">Verifikasi & Keputusan Permohonan</h2>
            </div>
            <button
              @click="handleClose"
              class="w-8 h-8 flex items-center justify-center bg-transparent border border-hairline rounded-sm text-mute cursor-pointer hover:bg-canvas transition-colors"
            >
              <Icon icon="mdi:close" />
            </button>
          </div>

          <!-- Content Body -->
          <div class="p-6 flex flex-col gap-5">
            <!-- Target Summary -->
            <div class="p-4 bg-surface-soft/60 rounded-sm border border-hairline flex flex-col gap-1.5 text-xs">
              <div class="flex items-center justify-between gap-2">
                <span class="font-mono text-mute font-bold">{{ application.code }}</span>
                <span class="px-2 py-0.5 bg-primary/10 text-primary rounded font-bold uppercase">
                  {{ application.document_type }}
                </span>
              </div>
              <h3 class="text-sm font-bold text-ink">{{ application.title }}</h3>
              <p class="text-body text-xs line-clamp-2">
                {{ application.description || 'Tidak ada catatan tambahan.' }}
              </p>
              <div v-if="application.applicant" class="text-[11px] text-mute mt-1">
                Pemohon: <span class="font-semibold text-ink">{{ application.applicant.name }}</span> ({{ application.applicant.email }})
              </div>
            </div>

            <!-- Decision Options -->
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold uppercase tracking-wider text-ink">
                Pilih Keputusan Penilaian *
              </label>
              <div class="grid grid-cols-3 gap-2.5">
                <button
                  v-for="opt in decisionOptions"
                  :key="opt.value"
                  type="button"
                  @click="decision = opt.value"
                  class="p-3 flex flex-col items-center gap-1.5 rounded-sm border cursor-pointer font-brand text-xs font-bold transition-all"
                  :class="decision === opt.value ? opt.activeClass : 'border-hairline hover:border-primary'"
                >
                  <Icon :icon="opt.icon" class="text-xl" :class="opt.iconClass" />
                  <span>{{ opt.label }}</span>
                </button>
              </div>
            </div>

            <!-- Review Notes Input -->
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-ink">
                Catatan Penilaian {{ isRevisionRequired ? '(Wajib Diisi)' : '(Opsional)' }}
              </label>
              <textarea
                v-model="note"
                rows="4"
                :required="isRevisionRequired"
                placeholder="Berikan alasan atau instruksi revisi yang jelas untuk pemohon..."
                class="w-full px-4 py-3 bg-canvas border border-hairline rounded-sm font-brand text-xs text-ink outline-none resize-y min-h-[90px] transition-colors focus:border-primary focus:border-2 focus:px-[15px] placeholder:text-ash"
              ></textarea>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-hairline bg-surface-soft">
            <button
              @click="handleClose"
              type="button"
              class="px-4 py-2 bg-transparent border border-hairline rounded-sm font-brand text-xs font-bold text-ink hover:bg-canvas cursor-pointer transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleSubmit"
              type="button"
              :disabled="!isValid || loading"
              class="inline-flex items-center gap-2 px-5 py-2 border-none rounded-sm font-brand text-xs font-bold cursor-pointer transition-all disabled:bg-surface-soft disabled:text-ash disabled:cursor-not-allowed text-ink bg-primary hover:bg-primary-dark shadow-xs"
            >
              <span v-if="loading" class="w-3.5 h-3.5 border-2 border-transparent border-t-current rounded-full animate-spin"></span>
              <span>Kirim Keputusan</span>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Icon } from '@iconify/vue'
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
    icon: 'mdi:check-circle',
    iconClass: 'text-emerald-600',
    activeClass: 'bg-emerald-500/10 border-emerald-500 border-2 text-emerald-700',
  },
  {
    value: 'revision_required',
    label: 'Perlu Revisi',
    icon: 'mdi:pencil-outline',
    iconClass: 'text-orange-500',
    activeClass: 'bg-orange-500/10 border-orange-500 border-2 text-orange-600',
  },
  {
    value: 'rejected',
    label: 'Ditolak',
    icon: 'mdi:close-circle',
    iconClass: 'text-error',
    activeClass: 'bg-error/10 border-error border-2 text-error',
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
