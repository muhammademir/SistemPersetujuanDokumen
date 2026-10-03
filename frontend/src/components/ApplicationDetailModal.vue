<template>
  <Dialog :open="modelValue" @update:open="emit('update:modelValue', $event)">
    <DialogContent class="sm:max-w-[760px] max-h-[90vh] flex flex-col p-0 gap-0 overflow-hidden border-gray-200">
      <!-- Header -->
      <div v-if="application" class="p-5 sm:p-6 border-b border-gray-100 flex items-start justify-between gap-4 bg-gray-50/60">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="text-xs font-mono text-gray-400 uppercase tracking-wider font-semibold">
              {{ application.code }}
            </span>
            <Badge variant="outline" class="text-[10px] uppercase font-bold border-gray-200 text-gray-700 bg-white">
              {{ application.document_type }}
            </Badge>
          </div>
          <DialogTitle class="text-base sm:text-lg font-bold text-gray-900">
            {{ application.title }}
          </DialogTitle>
        </div>
        <StatusBadge :status="application.status" />
      </div>

      <!-- Scrollable Body -->
      <div v-if="application" class="p-5 sm:p-6 overflow-y-auto space-y-6">
        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3.5 bg-gray-50 rounded-lg border border-gray-200 text-xs">
          <div>
            <span class="text-gray-400 block font-medium">Tipe Dokumen</span>
            <span class="font-bold text-gray-900 text-sm">{{ application.document_type }}</span>
          </div>
          <div>
            <span class="text-gray-400 block font-medium">Pemohon</span>
            <span class="font-bold text-gray-900 text-sm truncate block">{{ application.applicant?.name || '-' }}</span>
          </div>
          <div>
            <span class="text-gray-400 block font-medium">Tanggal Diajukan</span>
            <span class="font-bold text-gray-900">{{ formatDate(application.submitted_at || application.created_at) }}</span>
          </div>
          <div>
            <span class="text-gray-400 block font-medium">Revisi Ke</span>
            <span class="font-bold text-gray-900">#{{ application.revision_count }}</span>
          </div>
        </div>

        <!-- Description -->
        <div>
          <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Deskripsi / Keterangan Permohonan</h4>
          <div class="text-xs text-gray-700 leading-relaxed bg-gray-50/50 p-3.5 border border-gray-200 rounded-lg whitespace-pre-line">
            {{ application.description || 'Tidak ada keterangan tambahan.' }}
          </div>
        </div>

        <!-- Uploaded Documents -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">Berkas Lampiran Dokumen</h4>
            <Badge variant="secondary" class="text-xs font-semibold bg-gray-100 text-gray-600">
              {{ application.documents?.length || 0 }} Berkas
            </Badge>
          </div>

          <div v-if="application.documents && application.documents.length" class="space-y-2">
            <div
              v-for="doc in application.documents"
              :key="doc.id"
              class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:border-[#3b49f5]/50 transition-colors bg-white shadow-xs"
            >
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-lg bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                  <FileText class="w-5 h-5" />
                </div>
                <div class="flex flex-col min-w-0">
                  <span class="text-xs font-bold text-gray-800 truncate">{{ doc.original_name }}</span>
                  <span class="text-[11px] text-gray-400">
                    {{ formatFileSize(doc.size_bytes) }} · Revisi #{{ doc.revision_number }} · {{ formatDate(doc.uploaded_at) }}
                  </span>
                </div>
              </div>
              <Badge variant="outline" class="text-[10px] text-emerald-700 border-emerald-200 bg-emerald-50">
                Tersimpan
              </Badge>
            </div>
          </div>
          <div v-else class="p-4 border border-dashed border-gray-200 rounded-lg text-center text-xs text-gray-400">
            Belum ada berkas fisik yang diunggah.
          </div>
        </div>

        <!-- Reviews / Decision History -->
        <div v-if="application.reviews && application.reviews.length">
          <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Riwayat Catatan Penilai</h4>
          <div class="space-y-2.5">
            <div
              v-for="rev in application.reviews"
              :key="rev.id"
              class="p-3.5 rounded-lg border border-gray-200 bg-gray-50/60 space-y-1.5"
            >
              <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                  <Badge
                    :variant="rev.decision === 'approved' ? 'default' : rev.decision === 'rejected' ? 'destructive' : 'secondary'"
                    class="text-[11px] font-bold uppercase"
                  >
                    {{ formatDecision(rev.decision) }}
                  </Badge>
                  <span class="text-xs font-semibold text-gray-800">
                    Oleh {{ rev.reviewer || 'Penilai' }}
                  </span>
                </div>
                <span class="text-[11px] text-gray-400">{{ formatDate(rev.reviewed_at) }}</span>
              </div>
              <p class="text-xs text-gray-600 leading-relaxed">
                {{ rev.note || 'Tidak ada catatan khusus.' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Status Timeline -->
        <div v-if="timelineEvents.length">
          <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Timeline Riwayat Status</h4>
          <div class="relative pl-6 space-y-4 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200">
            <div
              v-for="(event, idx) in timelineEvents"
              :key="idx"
              class="relative flex items-start gap-3"
            >
              <div
                class="absolute -left-6 mt-0.5 w-5 h-5 rounded-full flex items-center justify-center ring-4 ring-white"
                :class="event.bgColor"
              >
                <component :is="event.icon" class="w-2.5 h-2.5 text-white" />
              </div>
              <div class="flex-1 pb-1">
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs font-bold text-gray-800">{{ event.status }}</span>
                  <span class="text-[10px] text-gray-400">{{ event.date }}</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-0.5">
                  Oleh <span class="font-medium text-gray-700">{{ event.actor }}</span>
                  <span v-if="event.note" class="italic"> · "{{ event.note }}"</span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <DialogFooter class="p-4 border-t border-gray-100 flex flex-row items-center justify-between sm:justify-between w-full bg-gray-50/50">
        <Button variant="outline" size="sm" @click="close">
          Tutup
        </Button>

        <div class="flex items-center gap-2">
          <!-- Pemohon Actions -->
          <template v-if="isPemohon && application">
            <Button
              v-if="statusVal === 'draft'"
              size="sm"
              class="gap-1.5 bg-[#3b49f5] hover:bg-[#2f3ce0] text-white"
              @click="$emit('submit-app', application)"
            >
              <Send class="w-3.5 h-3.5" />
              <span>Ajukan Sekarang</span>
            </Button>
            <Button
              v-if="statusVal === 'revision_required'"
              size="sm"
              variant="outline"
              class="gap-1.5 text-amber-600 border-amber-300 hover:bg-amber-50"
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
              class="gap-1.5 bg-[#3b49f5] hover:bg-[#2f3ce0] text-white"
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
    let bgColor = 'bg-gray-400'
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
