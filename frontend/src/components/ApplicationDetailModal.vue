<template>
  <Teleport to="body">
    <transition name="modal">
      <div v-if="modelValue && application" class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 sm:p-6 z-[100] backdrop-blur-xs" @click.self="close">
        <div class="bg-canvas rounded-sm w-full max-w-[760px] max-h-[92vh] flex flex-col overflow-hidden border border-hairline shadow-2xl animate-fade-in-up">
          
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-hairline bg-surface-soft">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 bg-primary"></div>
              <div>
                <span class="text-xs font-mono text-mute uppercase tracking-wider">{{ application.code }}</span>
                <h2 class="text-lg font-bold text-ink leading-tight">{{ application.title }}</h2>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <StatusBadge :status="application.status" />
              <button @click="close" class="w-8 h-8 flex items-center justify-center bg-transparent border border-hairline rounded-sm text-mute cursor-pointer hover:bg-canvas hover:text-ink transition-colors">
                <Icon icon="mdi:close" class="text-lg" />
              </button>
            </div>
          </div>

          <!-- Modal Body Scrollable -->
          <div class="flex-1 overflow-y-auto p-6 flex flex-col gap-6">
            
            <!-- Metadata Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-surface-soft/60 rounded-sm border border-hairline text-xs">
              <div>
                <span class="text-mute block font-medium">Tipe Dokumen</span>
                <span class="font-bold text-ink text-sm">{{ application.document_type }}</span>
              </div>
              <div>
                <span class="text-mute block font-medium">Pemohon</span>
                <span class="font-bold text-ink text-sm">{{ application.applicant?.name || '-' }}</span>
              </div>
              <div>
                <span class="text-mute block font-medium">Tanggal Diajukan</span>
                <span class="font-bold text-ink">{{ formatDate(application.submitted_at || application.created_at) }}</span>
              </div>
              <div>
                <span class="text-mute block font-medium">Revisi Ke</span>
                <span class="font-bold text-ink">{{ application.revision_count }}</span>
              </div>
            </div>

            <!-- Description -->
            <div>
              <h4 class="text-xs font-bold uppercase tracking-wider text-mute mb-1.5">Deskripsi / Keterangan Permohonan</h4>
              <p class="text-sm text-body leading-relaxed bg-canvas p-3 border border-hairline rounded-sm whitespace-pre-line">
                {{ application.description || 'Tidak ada keterangan tambahan.' }}
              </p>
            </div>

            <!-- Uploaded Documents -->
            <div>
              <div class="flex items-center justify-between mb-2">
                <h4 class="text-xs font-bold uppercase tracking-wider text-mute">Berkas Lampiran Dokumen</h4>
                <span class="text-xs text-mute">{{ application.documents?.length || 0 }} Berkas terlampir</span>
              </div>
              
              <div v-if="application.documents && application.documents.length" class="flex flex-col gap-2">
                <div
                  v-for="doc in application.documents"
                  :key="doc.id"
                  class="flex items-center justify-between p-3 border border-hairline rounded-sm bg-canvas hover:border-primary transition-colors"
                >
                  <div class="flex items-center gap-3 min-w-0">
                    <Icon icon="mdi:file-document-outline" class="text-xl text-primary flex-shrink-0" />
                    <div class="flex flex-col min-w-0">
                      <span class="text-xs font-bold text-ink truncate">{{ doc.original_name }}</span>
                      <span class="text-[11px] text-mute">
                        {{ formatFileSize(doc.size_bytes) }} · Revisi #{{ doc.revision_number }} · {{ formatDate(doc.uploaded_at) }}
                      </span>
                    </div>
                  </div>
                  <span class="text-xs font-bold text-primary px-2 py-1 bg-primary/10 rounded">Tersimpan</span>
                </div>
              </div>
              <div v-else class="p-4 border border-dashed border-hairline rounded-sm text-center text-xs text-mute">
                Belum ada berkas fisik yang diunggah.
              </div>
            </div>

            <!-- Reviews / Catatan Keputusan Penilai -->
            <div v-if="application.reviews && application.reviews.length">
              <h4 class="text-xs font-bold uppercase tracking-wider text-mute mb-2">Riwayat Catatan Penilai</h4>
              <div class="flex flex-col gap-2.5">
                <div
                  v-for="rev in application.reviews"
                  :key="rev.id"
                  class="p-3.5 rounded-sm border"
                  :class="rev.decision === 'approved' ? 'bg-emerald-500/5 border-emerald-500/30' : rev.decision === 'rejected' ? 'bg-error/5 border-error/30' : 'bg-warning/5 border-warning/30'"
                >
                  <div class="flex items-center justify-between gap-2 mb-1.5">
                    <div class="flex items-center gap-2">
                      <span
                        class="text-xs font-bold uppercase px-2 py-0.5 rounded"
                        :class="rev.decision === 'approved' ? 'bg-emerald-500 text-white' : rev.decision === 'rejected' ? 'bg-error text-white' : 'bg-warning text-white'"
                      >
                        {{ formatDecision(rev.decision) }}
                      </span>
                      <span class="text-xs font-semibold text-ink">Oleh {{ rev.reviewer || 'Penilai' }}</span>
                    </div>
                    <span class="text-[11px] text-mute">{{ formatDate(rev.reviewed_at) }}</span>
                  </div>
                  <p class="text-xs text-body leading-relaxed mt-1">
                    {{ rev.note || 'Tidak ada catatan khusus.' }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Status Logs Timeline -->
            <div v-if="application.status_logs && application.status_logs.length">
              <h4 class="text-xs font-bold uppercase tracking-wider text-mute mb-2">Log Riwayat Perubahan Status</h4>
              <div class="relative pl-6 flex flex-col gap-3 border-l-2 border-hairline ml-2">
                <div
                  v-for="log in application.status_logs"
                  :key="log.id"
                  class="relative flex flex-col text-xs"
                >
                  <div class="absolute -left-[31px] top-0.5 w-2.5 h-2.5 rounded-full bg-primary ring-4 ring-canvas"></div>
                  <div class="flex items-center justify-between gap-2">
                    <span class="font-bold text-ink">
                      {{ formatStatusName(log.to_status) }}
                    </span>
                    <span class="text-[11px] text-mute">{{ formatDate(log.created_at) }}</span>
                  </div>
                  <span class="text-mute text-[11px] mt-0.5">
                    Oleh {{ log.actor || 'Sistem' }}
                    <span v-if="log.note"> · "{{ log.note }}"</span>
                  </span>
                </div>
              </div>
            </div>

          </div>

          <!-- Modal Footer Actions -->
          <div class="flex items-center justify-between px-6 py-4 border-t border-hairline bg-surface-soft">
            <button @click="close" class="px-4 py-2 bg-transparent border border-hairline rounded-sm font-brand text-xs font-bold text-ink hover:bg-canvas transition-colors">
              Tutup
            </button>

            <div class="flex items-center gap-2">
              <!-- Pemohon Actions -->
              <template v-if="isPemohon">
                <button
                  v-if="statusVal === 'draft'"
                  @click="$emit('submit-app', application)"
                  class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-ink font-brand text-xs font-bold rounded-sm hover:bg-primary-dark transition-colors"
                >
                  <Icon icon="mdi:send" /> Ajukan Sekarang
                </button>
                <button
                  v-if="statusVal === 'revision_required'"
                  @click="$emit('edit-app', application)"
                  class="inline-flex items-center gap-1.5 px-4 py-2 bg-warning text-white font-brand text-xs font-bold rounded-sm hover:brightness-110 transition-all"
                >
                  <Icon icon="mdi:pencil" /> Perbaiki Revisi
                </button>
              </template>

              <!-- Penilai Actions -->
              <template v-if="isPenilai">
                <button
                  v-if="statusVal === 'submitted' || statusVal === 'under_review'"
                  @click="$emit('open-review', application)"
                  class="inline-flex items-center gap-1.5 px-5 py-2 bg-primary text-ink font-brand text-xs font-bold rounded-sm hover:bg-primary-dark transition-colors"
                >
                  <Icon icon="mdi:check-decagram-outline" /> Beri Penilaian
                </button>
              </template>
            </div>
          </div>

        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
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

function close() {
  emit('update:modelValue', false)
}
</script>
