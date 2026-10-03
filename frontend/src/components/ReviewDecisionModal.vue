<template>
  <Dialog :open="modelValue" @update:open="emit('update:modelValue', $event)">
    <DialogContent class="sm:max-w-[560px] p-6 border-gray-200">
      <DialogHeader>
        <DialogTitle class="text-base font-bold text-gray-900">Verifikasi & Keputusan Permohonan</DialogTitle>
        <DialogDescription class="text-xs text-gray-500">
          Tentukan status persetujuan untuk dokumen permohonan ini.
        </DialogDescription>
      </DialogHeader>

      <div v-if="application" class="space-y-4 py-2">
        <!-- Target Summary Box -->
        <div class="p-3.5 bg-gray-50 rounded-lg border border-gray-200 flex flex-col gap-1.5 text-xs">
          <div class="flex items-center justify-between gap-2">
            <span class="font-mono text-gray-500 font-bold">{{ application.code }}</span>
            <Badge variant="secondary" class="text-[10px] font-bold uppercase bg-gray-200/70 text-gray-700">
              {{ application.document_type }}
            </Badge>
          </div>
          <h3 class="text-sm font-bold text-gray-900">{{ application.title }}</h3>
          <p class="text-gray-500 text-xs line-clamp-2">
            {{ application.description || 'Tidak ada catatan tambahan.' }}
          </p>
          <div v-if="application.applicant" class="text-[11px] text-gray-400 mt-1">
            Pemohon: <span class="font-semibold text-gray-700">{{ application.applicant.name }}</span> ({{ application.applicant.email }})
          </div>
        </div>

        <!-- Decision Options -->
        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
            Pilih Keputusan Penilaian *
          </label>
          <div class="grid grid-cols-3 gap-3">
            <div
              v-for="opt in decisionOptions"
              :key="opt.value"
              @click="decision = opt.value"
              class="p-3.5 flex flex-col items-center gap-2 rounded-lg border-2 cursor-pointer transition-all text-center select-none"
              :class="decision === opt.value ? opt.activeClass : 'border-gray-200 hover:border-gray-300 bg-white text-gray-600'"
            >
              <component :is="opt.icon" class="w-6 h-6" :class="opt.iconClass" />
              <span class="text-xs font-bold">{{ opt.label }}</span>
            </div>
          </div>
        </div>

        <!-- Review Notes Input -->
        <div class="space-y-1.5">
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
            Catatan Penilaian {{ isRevisionRequired ? '(Wajib Diisi)' : '(Opsional)' }}
          </label>
          <Textarea
            v-model="note"
            rows="4"
            class="w-full text-xs resize-none border-gray-200 focus:border-[#3b49f5]"
            placeholder="Berikan alasan atau instruksi revisi yang jelas untuk pemohon..."
          />
        </div>
      </div>

      <DialogFooter class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
        <Button variant="outline" size="sm" @click="handleClose">
          Batal
        </Button>
        <Button
          size="sm"
          :disabled="!isValid || loading"
          @click="handleSubmit"
          class="gap-1.5 bg-[#3b49f5] hover:bg-[#2f3ce0] text-white"
        >
          <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
          <CheckCircle2 v-else class="w-4 h-4" />
          <span>Kirim Keputusan</span>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Textarea } from '@/components/ui/textarea'
import { CheckCircle2, AlertTriangle, XCircle, Loader2 } from 'lucide-vue-next'
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
    icon: CheckCircle2,
    iconClass: 'text-emerald-500',
    activeClass: 'border-emerald-500 bg-emerald-50 text-emerald-700',
  },
  {
    value: 'revision_required',
    label: 'Perlu Revisi',
    icon: AlertTriangle,
    iconClass: 'text-amber-500',
    activeClass: 'border-amber-500 bg-amber-50 text-amber-700',
  },
  {
    value: 'rejected',
    label: 'Ditolak',
    icon: XCircle,
    iconClass: 'text-red-500',
    activeClass: 'border-red-500 bg-red-50 text-red-700',
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
