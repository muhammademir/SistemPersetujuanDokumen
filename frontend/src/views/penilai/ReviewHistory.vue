<template>
  <div class="max-w-[1240px] mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="font-brand text-2xl font-bold text-ink mb-1">Riwayat Hasil Penilaian</h1>
        <p class="text-xs text-mute">Daftar seluruh keputusan verifikasi dan review yang telah Anda berikan</p>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xs text-mute">Total Riwayat: <strong class="text-ink">{{ historyReviews.length }}</strong></span>
      </div>
    </div>

    <!-- Table of Reviews -->
    <div class="bg-canvas border border-hairline rounded-sm p-6 overflow-hidden flex flex-col gap-4">
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-xs">
          <thead>
            <tr class="bg-surface-soft/60 border-b border-hairline">
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-mute">Waktu Penilaian</th>
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-mute">Permohonan Dokumen</th>
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-mute">Keputusan</th>
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-mute">Catatan Reviewer</th>
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-mute text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-hairline">
            <tr
              v-for="rev in historyReviews"
              :key="rev.id"
              class="hover:bg-surface-soft/40 transition-colors"
            >
              <td class="py-3 px-3 whitespace-nowrap text-mute font-medium">
                {{ formatDateTime(rev.reviewed_at || rev.created_at) }}
              </td>
              <td class="py-3 px-3">
                <div class="flex flex-col">
                  <div class="flex items-center gap-1.5">
                    <span class="font-mono text-mute text-[10px]">{{ rev.application?.code || '-' }}</span>
                    <span v-if="rev.application?.document_type" class="px-1 py-0.2 bg-primary/10 text-primary rounded text-[9px] font-bold">
                      {{ rev.application.document_type }}
                    </span>
                  </div>
                  <span class="font-bold text-ink text-xs mt-0.5 max-w-[280px] truncate">
                    {{ rev.application?.title || 'Permohonan' }}
                  </span>
                </div>
              </td>
              <td class="py-3 px-3 whitespace-nowrap">
                <span
                  class="px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wide border"
                  :class="getDecisionBadgeClass(rev.decision)"
                >
                  {{ formatDecision(rev.decision) }}
                </span>
              </td>
              <td class="py-3 px-3">
                <p class="text-xs text-body line-clamp-2 max-w-[320px]">
                  {{ rev.note || '-' }}
                </p>
              </td>
              <td class="py-3 px-3 text-right whitespace-nowrap">
                <button
                  v-if="rev.application_id"
                  @click="viewAppDetail(rev.application_id)"
                  class="px-3 py-1.5 bg-surface-soft border border-hairline rounded-sm text-ink font-bold hover:bg-hairline cursor-pointer transition-colors"
                >
                  Detail
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="!historyReviews.length && !loading" class="flex flex-col items-center justify-center py-16 text-mute text-xs gap-2">
        <Icon icon="mdi:history" class="text-4xl text-mute" />
        <h3 class="text-sm font-bold text-ink">Belum Ada Riwayat Penilaian</h3>
        <p class="text-xs text-mute max-w-[320px] text-center">Setiap permohonan yang Anda setujui, tolak, atau minta revisi akan tercatat di halaman ini.</p>
      </div>
    </div>

    <!-- Detail Modal -->
    <ApplicationDetailModal
      v-model="showDetailModal"
      :application="selectedApplication"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Icon } from '@iconify/vue'
import api from '@/plugins/axios'
import { useDocumentStore } from '@/stores/document'
import ApplicationDetailModal from '@/components/ApplicationDetailModal.vue'
import type { Application } from '@/types'

const docStore = useDocumentStore()

const historyReviews = ref<any[]>([])
const loading = ref(false)
const showDetailModal = ref(false)
const selectedApplication = ref<Application | null>(null)

async function fetchHistory() {
  loading.value = true
  try {
    const res = await api.get('/reviews/history')
    historyReviews.value = res.data?.data ?? res.data ?? []
  } catch (e) {
    console.error('Failed to load history', e)
  } finally {
    loading.value = false
  }
}

async function viewAppDetail(appId: number) {
  await docStore.fetchApplication(appId)
  selectedApplication.value = docStore.currentApplication
  showDetailModal.value = true
}

function formatDateTime(d?: string) {
  if (!d) return '-'
  return new Date(d).toLocaleString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

function formatDecision(dec: string) {
  const map: Record<string, string> = {
    approved: 'Disetujui',
    revision_required: 'Perlu Revisi',
    rejected: 'Ditolak',
  }
  return map[dec] ?? dec
}

function getDecisionBadgeClass(dec: string) {
  switch (dec) {
    case 'approved':
      return 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700'
    case 'revision_required':
      return 'bg-orange-500/10 border-orange-500/30 text-orange-600'
    case 'rejected':
      return 'bg-error/10 border-error/30 text-error'
    default:
      return 'bg-surface-soft border-hairline text-mute'
  }
}

onMounted(() => {
  fetchHistory()
})
</script>
