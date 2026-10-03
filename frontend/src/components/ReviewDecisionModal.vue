<template>
  <Dialog :open="modelValue" @update:open="emit('update:modelValue', $event)">
    <DialogContent class="sm:max-w-[600px]">
      <DialogHeader>
        <DialogTitle>Verifikasi & Keputusan Permohonan</DialogTitle>
        <DialogDescription>
          Tentukan status persetujuan untuk dokumen permohonan ini.
        </DialogDescription>
      </DialogHeader>

      <div v-if="application" class="space-y-4 py-2">
        <!-- Target Summary Box -->
        <div class="p-3.5 bg-muted/50 rounded-lg border flex flex-col gap-1.5 text-xs">
          <div class="flex items-center justify-between gap-2">
            <span class="font-mono text-muted-foreground font-bold">{{ application.code }}</span>
            <Badge variant="secondary" class="text-[10px] font-bold uppercase">
              {{ application.document_type }}
            </Badge>
          </div>
          <h3 class="text-sm font-bold text-foreground">{{ application.title }}</h3>
          <p class="text-muted-foreground text-xs line-clamp-2">
            {{ application.description || 'Tidak ada catatan tambahan.' }}
          </p>
          <div v-if="application.applicant" class="text-[11px] text-muted-foreground mt-1">
            Pemohon: <span class="font-semibold text-foreground">{{ application.applicant.name }}</span> ({{ application.applicant.email }})
          </div>
        </div>

        <!-- Decision Options -->
        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-foreground">
            Pilih Keputusan Penilaian *
          </label>
          <div class="grid grid-cols-3 gap-3">
            <div
              v-for="opt in decisionOptions"
              :key="opt.value"
              @click="decision = opt.value"
              class="p-3.5 flex flex-col items-center gap-2 rounded-lg border-2 cursor-pointer transition-all text-center select-none"
              :class="decision === opt.value ? opt.activeClass : 'border-border hover:border-foreground/30'"
            >
              <component :is="opt.icon" class="w-6 h-6" :class="opt.iconClass" />
              <span class="text-xs font-bold">{{ opt.label }}</span>
            </div>
          </div>
        </div>

        <!-- Review Notes Input -->
        <div class="space-y-1.5">
          <label class="block text-xs font-bold uppercase tracking-wider text-foreground">
            Catatan Penilaian {{ isRevisionRequired ? '(Wajib Diisi)' : '(Opsional)' }}
          </label>
          <Textarea
            v-model="note"
            rows="4"
            class="w-full text-xs resize-none"
            placeholder="Berikan alasan atau instruksi revisi yang jelas untuk pemohon..."
          />
        </div>
      </div>

      <DialogFooter class="flex items-center justify-end gap-2 pt-2">
        <Button variant="outline" size="sm" @click="handleClose">
          Batal
        </Button>
        <Button
          size="sm"
          :disabled="!isValid || loading"
          @click="handleSubmit"
          class="gap-1.5"
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
    activeClass: 'border-emerald-500 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
  },
  {
    value: 'revision_required',
    label: 'Perlu Revisi',
    icon: AlertTriangle,
    iconClass: 'text-amber-500',
    activeClass: 'border-amber-500 bg-amber-500/10 text-amber-600 dark:text-amber-400',
  },
  {
    value: 'rejected',
    label: 'Ditolak',
    icon: XCircle,
    iconClass: 'text-red-500',
    activeClass: 'border-red-500 bg-red-500/10 text-red-600 dark:text-red-400',
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
