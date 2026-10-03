<template>
  <Dialog
    :visible="modelValue"
    @update:visible="emit('update:modelValue', $event)"
    modal
    :header="application ? `${application.code} - ${application.title}` : 'Detail Permohonan'"
    :style="{ width: '90vw', maxWidth: '780px' }"
    :breakpoints="{ '960px': '95vw', '640px': '100vw' }"
  >
    <template #header v-if="application">
      <div class="flex items-center justify-between w-full pr-4">
        <div class="flex items-center gap-3">
          <div class="w-3 h-3 bg-primary-500 rounded-sm"></div>
          <div>
            <span class="text-xs font-mono text-surface-500 uppercase tracking-wider block">
              {{ application.code }}
            </span>
            <h2 class="text-base font-bold text-surface-900 dark:text-surface-0 leading-tight">
              {{ application.title }}
            </h2>
          </div>
        </div>
        <StatusBadge :status="application.status" />
      </div>
    </template>

    <div v-if="application" class="space-y-6 pt-2">
      <!-- Metadata Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-surface-50 dark:bg-surface-800/60 rounded-lg border border-surface-200 dark:border-surface-700 text-xs">
        <div>
          <span class="text-surface-500 block font-medium">Tipe Dokumen</span>
          <span class="font-bold text-surface-900 dark:text-surface-0 text-sm">{{ application.document_type }}</span>
        </div>
        <div>
          <span class="text-surface-500 block font-medium">Pemohon</span>
          <span class="font-bold text-surface-900 dark:text-surface-0 text-sm">{{ application.applicant?.name || '-' }}</span>
        </div>
        <div>
          <span class="text-surface-500 block font-medium">Tanggal Diajukan</span>
          <span class="font-bold text-surface-900 dark:text-surface-0">{{ formatDate(application.submitted_at || application.created_at) }}</span>
        </div>
        <div>
          <span class="text-surface-500 block font-medium">Revisi Ke</span>
          <span class="font-bold text-surface-900 dark:text-surface-0">{{ application.revision_count }}</span>
        </div>
      </div>

      <!-- Description -->
      <div>
        <h4 class="text-xs font-bold uppercase tracking-wider text-surface-500 mb-2">Deskripsi / Keterangan Permohonan</h4>
        <div class="text-sm text-surface-700 dark:text-surface-300 leading-relaxed bg-surface-50 dark:bg-surface-800/40 p-3.5 border border-surface-200 dark:border-surface-700 rounded-lg whitespace-pre-line">
          {{ application.description || 'Tidak ada keterangan tambahan.' }}
        </div>
      </div>

      <!-- Uploaded Documents -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <h4 class="text-xs font-bold uppercase tracking-wider text-surface-500">Berkas Lampiran Dokumen</h4>
          <Tag :value="`${application.documents?.length || 0} Berkas`" severity="secondary" rounded />
        </div>

        <div v-if="application.documents && application.documents.length" class="space-y-2">
          <div
            v-for="doc in application.documents"
            :key="doc.id"
            class="flex items-center justify-between p-3 border border-surface-200 dark:border-surface-700 rounded-lg bg-surface-0 dark:bg-surface-900 hover:border-primary-500 transition-colors"
          >
            <div class="flex items-center gap-3 min-w-0">
              <i class="pi pi-file-pdf text-2xl text-primary-500 flex-shrink-0"></i>
              <div class="flex flex-col min-w-0">
                <span class="text-xs font-bold text-surface-900 dark:text-surface-0 truncate">{{ doc.original_name }}</span>
                <span class="text-[11px] text-surface-500">
                  {{ formatFileSize(doc.size_bytes) }} · Revisi #{{ doc.revision_number }} · {{ formatDate(doc.uploaded_at) }}
                </span>
              </div>
            </div>
            <Tag value="Tersimpan" severity="success" class="text-xs font-bold" />
          </div>
        </div>
        <div v-else class="p-4 border border-dashed border-surface-300 dark:border-surface-700 rounded-lg text-center text-xs text-surface-500">
          Belum ada berkas fisik yang diunggah.
        </div>
      </div>

      <!-- Reviews / Decision History -->
      <div v-if="application.reviews && application.reviews.length">
        <h4 class="text-xs font-bold uppercase tracking-wider text-surface-500 mb-2">Riwayat Catatan Penilai</h4>
        <div class="space-y-3">
          <div
            v-for="rev in application.reviews"
            :key="rev.id"
            class="p-4 rounded-lg border border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800/40"
          >
            <div class="flex items-center justify-between gap-2 mb-2">
              <div class="flex items-center gap-2">
                <Tag
                  :value="formatDecision(rev.decision)"
                  :severity="rev.decision === 'approved' ? 'success' : rev.decision === 'rejected' ? 'danger' : 'warn'"
                  class="text-xs font-bold uppercase"
                />
                <span class="text-xs font-semibold text-surface-800 dark:text-surface-200">
                  Oleh {{ rev.reviewer || 'Penilai' }}
                </span>
              </div>
              <span class="text-[11px] text-surface-500">{{ formatDate(rev.reviewed_at) }}</span>
            </div>
            <p class="text-xs text-surface-700 dark:text-surface-300 leading-relaxed mt-1">
              {{ rev.note || 'Tidak ada catatan khusus.' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Status Timeline with PrimeVue Timeline -->
      <div v-if="timelineEvents.length">
        <h4 class="text-xs font-bold uppercase tracking-wider text-surface-500 mb-2">Timeline Riwayat Status</h4>
        <Timeline :value="timelineEvents">
          <template #marker="slotProps">
            <span
              class="flex items-center justify-center w-6 h-6 rounded-full text-white text-[10px]"
              :class="slotProps.item.color"
            >
              <i :class="slotProps.item.icon"></i>
            </span>
          </template>
          <template #content="slotProps">
            <div class="pb-3 text-xs">
              <div class="flex items-center justify-between gap-2">
                <span class="font-bold text-surface-900 dark:text-surface-0">{{ slotProps.item.status }}</span>
                <span class="text-[11px] text-surface-500">{{ slotProps.item.date }}</span>
              </div>
              <span class="text-[11px] text-surface-500 block mt-0.5">
                Oleh {{ slotProps.item.actor }}
                <span v-if="slotProps.item.note"> · "{{ slotProps.item.note }}"</span>
              </span>
            </div>
          </template>
        </Timeline>
      </div>
    </div>

    <template #footer>
      <div class="flex items-center justify-between w-full pt-2">
        <Button label="Tutup" severity="secondary" text @click="close" />

        <div class="flex items-center gap-2">
          <!-- Pemohon Actions -->
          <template v-if="isPemohon && application">
            <Button
              v-if="statusVal === 'draft'"
              label="Ajukan Sekarang"
              icon="pi pi-send"
              severity="primary"
              @click="$emit('submit-app', application)"
            />
            <Button
              v-if="statusVal === 'revision_required'"
              label="Perbaiki Revisi"
              icon="pi pi-pencil"
              severity="warn"
              @click="$emit('edit-app', application)"
            />
          </template>

          <!-- Penilai Actions -->
          <template v-if="isPenilai && application">
            <Button
              v-if="statusVal === 'submitted' || statusVal === 'under_review'"
              label="Beri Penilaian"
              icon="pi pi-check-square"
              severity="primary"
              @click="$emit('open-review', application)"
            />
          </template>
        </div>
      </div>
    </template>
  </Dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Timeline from 'primevue/timeline'
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
    let icon = 'pi pi-info-circle'
    let color = 'bg-surface-500'
    const st = log.to_status

    if (st === 'approved') {
      icon = 'pi pi-check'
      color = 'bg-emerald-600'
    } else if (st === 'rejected') {
      icon = 'pi pi-times'
      color = 'bg-red-600'
    } else if (st === 'revision_required') {
      icon = 'pi pi-pencil'
      color = 'bg-amber-600'
    } else if (st === 'submitted') {
      icon = 'pi pi-send'
      color = 'bg-blue-600'
    } else if (st === 'under_review') {
      icon = 'pi pi-search'
      color = 'bg-indigo-600'
    }

    return {
      status: formatStatusName(st),
      date: formatDate(log.created_at),
      actor: log.actor || 'Sistem',
      note: log.note,
      icon,
      color,
    }
  })
})

function close() {
  emit('update:modelValue', false)
}
</script>
