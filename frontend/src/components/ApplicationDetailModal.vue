<template>
  <Dialog :open="modelValue" @update:open="emit('update:modelValue', $event)">
    <DialogContent class="sm:max-w-[760px] max-h-[90vh] flex flex-col p-0 gap-0 overflow-hidden">
      <!-- Header -->
      <div v-if="application" class="p-6 border-b flex items-start justify-between gap-4 bg-muted/20">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="text-xs font-mono text-muted-foreground uppercase tracking-wider font-semibold">
              {{ application.code }}
            </span>
            <Badge variant="outline" class="text-[10px] uppercase font-bold">
              {{ application.document_type }}
            </Badge>
          </div>
          <DialogTitle class="text-lg font-bold text-foreground">
            {{ application.title }}
          </DialogTitle>
        </div>
        <StatusBadge :status="application.status" />
      </div>

      <!-- Scrollable Body -->
      <div v-if="application" class="p-6 overflow-y-auto space-y-6">
        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3.5 bg-muted/40 rounded-lg border text-xs">
          <div>
            <span class="text-muted-foreground block font-medium">Tipe Dokumen</span>
            <span class="font-bold text-foreground text-sm">{{ application.document_type }}</span>
          </div>
          <div>
            <span class="text-muted-foreground block font-medium">Pemohon</span>
            <span class="font-bold text-foreground text-sm truncate block">{{ application.applicant?.name || '-' }}</span>
          </div>
          <div>
            <span class="text-muted-foreground block font-medium">Tanggal Diajukan</span>
            <span class="font-bold text-foreground">{{ formatDate(application.submitted_at || application.created_at) }}</span>
          </div>
          <div>
            <span class="text-muted-foreground block font-medium">Revisi Ke</span>
            <span class="font-bold text-foreground">#{{ application.revision_count }}</span>
          </div>
        </div>

        <!-- Description -->
        <div>
          <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">Deskripsi / Keterangan Permohonan</h4>
          <div class="text-xs text-foreground leading-relaxed bg-muted/20 p-3.5 border rounded-lg whitespace-pre-line">
            {{ application.description || 'Tidak ada keterangan tambahan.' }}
          </div>
        </div>

        <!-- Uploaded Documents -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Berkas Lampiran Dokumen</h4>
            <Badge variant="secondary" class="text-xs font-semibold">
              {{ application.documents?.length || 0 }} Berkas
            </Badge>
          </div>

          <div v-if="application.documents && application.documents.length" class="space-y-2">
            <div
              v-for="doc in application.documents"
              :key="doc.id"
              class="flex items-center justify-between p-3 border rounded-lg hover:border-primary/50 transition-colors bg-card"
            >
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-lg bg-red-500/10 text-red-600 flex items-center justify-center shrink-0">
                  <FileText class="w-5 h-5" />
                </div>
                <div class="flex flex-col min-w-0">
                  <span class="text-xs font-bold text-foreground truncate">{{ doc.original_name }}</span>
                  <span class="text-[11px] text-muted-foreground">
                    {{ formatFileSize(doc.size_bytes) }} · Revisi #{{ doc.revision_number }} · {{ formatDate(doc.uploaded_at) }}
                  </span>
                </div>
              </div>
              <Badge variant="outline" class="text-[10px] text-emerald-600 border-emerald-500/30 bg-emerald-500/10">
                Tersimpan
              </Badge>
            </div>
          </div>
          <div v-else class="p-4 border border-dashed rounded-lg text-center text-xs text-muted-foreground">
            Belum ada berkas fisik yang diunggah.
          </div>
        </div>

        <!-- Reviews / Decision History -->
        <div v-if="application.reviews && application.reviews.length">
          <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">Riwayat Catatan Penilai</h4>
          <div class="space-y-2.5">
            <div
              v-for="rev in application.reviews"
              :key="rev.id"
              class="p-3.5 rounded-lg border bg-muted/20 space-y-1.5"
            >
              <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                  <Badge
                    :variant="rev.decision === 'approved' ? 'default' : rev.decision === 'rejected' ? 'destructive' : 'secondary'"
                    class="text-[11px] font-bold uppercase"
                  >
                    {{ formatDecision(rev.decision) }}
                  </Badge>
                  <span class="text-xs font-semibold text-foreground">
                    Oleh {{ rev.reviewer || 'Penilai' }}
                  </span>
                </div>
                <span class="text-[11px] text-muted-foreground">{{ formatDate(rev.reviewed_at) }}</span>
              </div>
              <p class="text-xs text-muted-foreground leading-relaxed">
                {{ rev.note || 'Tidak ada catatan khusus.' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Status Timeline -->
        <div v-if="timelineEvents.length">
          <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Timeline Riwayat Status</h4>
          <div class="relative pl-6 space-y-4 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-border">
            <div
              v-for="(event, idx) in timelineEvents"
              :key="idx"
              class="relative flex items-start gap-3"
            >
              <div
                class="absolute -left-6 mt-0.5 w-5 h-5 rounded-full flex items-center justify-center ring-4 ring-background"
                :class="event.bgColor"
              >
                <component :is="event.icon" class="w-2.5 h-2.5 text-white" />
              </div>
              <div class="flex-1 pb-1">
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs font-bold text-foreground">{{ event.status }}</span>
                  <span class="text-[10px] text-muted-foreground">{{ event.date }}</span>
                </div>
                <p class="text-[11px] text-muted-foreground mt-0.5">
                  Oleh <span class="font-medium text-foreground">{{ event.actor }}</span>
                  <span v-if="event.note" class="italic"> · "{{ event.note }}"</span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <DialogFooter class="p-4 border-t flex flex-row items-center justify-between sm:justify-between w-full bg-muted/20">
        <Button variant="outline" size="sm" @click="close">
          Tutup
        </Button>

        <div class="flex items-center gap-2">
          <!-- Pemohon Actions -->
          <template v-if="isPemohon && application">
            <Button
              v-if="statusVal === 'draft'"
              size="sm"
              class="gap-1.5"
              @click="$emit('submit-app', application)"
            >
              <Send class="w-3.5 h-3.5" />
              <span>Ajukan Sekarang</span>
            </Button>
            <Button
              v-if="statusVal === 'revision_required'"
              size="sm"
              variant="outline"
              class="gap-1.5 text-amber-600 border-amber-500/40 hover:bg-amber-500/10"
              @click="$emit('edit-app', application)"
            >
              <Pencil class="w-3.5 h-3.5" />
              <span>Perbaiki Revisi</span>
            </Button>
          </template>

          <!-- Penilai Actions -->
          <template v-if="isPenilai && application">
            <Button
              v-if="statusVal === 'submitted' || statusVal === 'under_review'"
              size="sm"
              class="gap-1.5"
              @click="$emit('open-review', application)"
            >
              <CheckSquare class="w-3.5 h-3.5" />
              <span>Beri Penilaian</span>
            </Button>
          </template>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  Dialog,
  DialogContent,
  DialogTitle,
  DialogFooter,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
  FileText,
  Send,
  Pencil,
  CheckSquare,
  Check,
  X,
  Search,
  Info
} from 'lucide-vue-next'
import type { Application } from '@/types'
import StatusBadge from './StatusBadge.vue'
import { useAuthStore } from '@/stores/auth'
import {
  formatDateTime as formatDate,
  formatFileSize,
  formatDecision,
  formatStatusName,
  getStatusValue,
} from '@/utils/formatters'

const props = defineProps<{
  modelValue: boolean
  application: Application | null
}>()

const emit = defineEmits<{
  'update:modelValue': [val: boolean]
  'submit-app': [app: Application]
  'edit-app': [app: Application]
  'open-review': [app: Application]
}>()

const authStore = useAuthStore()
const isPemohon = computed(() => authStore.isPemohon)
const isPenilai = computed(() => authStore.isPenilai)

const statusVal = computed(() => {
  if (!props.application) return ''
  return getStatusValue(props.application.status)
})

const timelineEvents = computed(() => {
  if (!props.application?.status_logs) return []
  return props.application.status_logs.map(log => {
    let icon = Info
    let bgColor = 'bg-muted-foreground'
    const st = log.to_status

    if (st === 'approved') {
      icon = Check
      bgColor = 'bg-emerald-600'
    } else if (st === 'rejected') {
      icon = X
      bgColor = 'bg-red-600'
    } else if (st === 'revision_required') {
      icon = Pencil
      bgColor = 'bg-amber-600'
    } else if (st === 'submitted') {
      icon = Send
      bgColor = 'bg-blue-600'
    } else if (st === 'under_review') {
      icon = Search
      bgColor = 'bg-indigo-600'
    }

    return {
      status: formatStatusName(st),
      date: formatDate(log.created_at),
      actor: log.actor || 'Sistem',
      note: log.note,
      icon,
      bgColor,
    }
  })
})

function close() {
  emit('update:modelValue', false)
}
</script>
