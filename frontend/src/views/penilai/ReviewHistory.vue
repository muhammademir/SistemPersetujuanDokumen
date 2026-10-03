<template>
  <div class="max-w-[1240px] mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="font-brand text-2xl font-bold text-surface-900 dark:text-surface-0 mb-1">
          Riwayat Hasil Penilaian
        </h1>
        <p class="text-xs text-surface-500">
          Daftar seluruh keputusan verifikasi dan review yang telah Anda berikan
        </p>
      </div>
      <Tag
        :value="`Total Riwayat: ${historyReviews.length}`"
        severity="secondary"
        class="text-xs font-bold px-3 py-1.5"
      />
    </div>

    <!-- Table of Reviews using PrimeVue DataTable -->
    <Card class="border border-surface-200 dark:border-surface-700 shadow-sm overflow-hidden">
      <template #content>
        <DataTable
          :value="historyReviews"
          paginator
          :rows="10"
          :rowsPerPageOptions="[5, 10, 20, 50]"
          responsiveLayout="scroll"
          class="text-xs"
          stripedRows
          :loading="loading"
        >
          <template #empty>
            <div class="flex flex-col items-center justify-center py-16 text-surface-400 text-xs gap-2">
              <i class="pi pi-history text-4xl mb-1"></i>
              <h3 class="text-sm font-bold text-surface-900 dark:text-surface-0">Belum Ada Riwayat Penilaian</h3>
              <p class="text-xs text-surface-500 max-w-[320px] text-center">
                Setiap permohonan yang Anda setujui, tolak, atau minta revisi akan tercatat di halaman ini.
              </p>
            </div>
          </template>

          <Column header="Waktu Penilaian" style="min-width: 150px">
            <template #body="{ data }">
              <span class="text-surface-500 font-medium">
                {{ formatDateTime(data.reviewed_at || data.created_at) }}
              </span>
            </template>
          </Column>

          <Column header="Permohonan Dokumen" style="min-width: 260px">
            <template #body="{ data }">
              <div class="flex flex-col">
                <div class="flex items-center gap-1.5 mb-1">
                  <span class="font-mono text-surface-500 text-[10px]">{{ data.application?.code || '-' }}</span>
                  <Tag
                    v-if="data.application?.document_type"
                    :value="data.application.document_type"
                    severity="info"
                    class="text-[9px] font-bold"
                  />
                </div>
                <span class="font-bold text-surface-900 dark:text-surface-0 text-xs truncate max-w-[280px]">
                  {{ data.application?.title || 'Permohonan' }}
                </span>
              </div>
            </template>
          </Column>

          <Column header="Keputusan" style="min-width: 140px">
            <template #body="{ data }">
              <Tag
                :value="formatDecision(data.decision)"
                :severity="getDecisionSeverity(data.decision)"
                class="text-[10px] font-bold uppercase"
              />
            </template>
          </Column>

          <Column header="Catatan Reviewer" style="min-width: 280px">
            <template #body="{ data }">
              <p class="text-xs text-surface-700 dark:text-surface-300 line-clamp-2 max-w-[320px]">
                {{ data.note || '-' }}
              </p>
            </template>
          </Column>

          <Column header="Aksi" style="min-width: 100px; text-align: right">
            <template #body="{ data }">
              <Button
                v-if="data.application_id"
                label="Detail"
                icon="pi pi-eye"
                size="small"
                severity="secondary"
                text
                @click="viewAppDetail(data.application_id)"
              />
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>

    <!-- Detail Modal -->
    <ApplicationDetailModal
      v-model="showDetailModal"
      :application="selectedApplication"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import api from '@/plugins/axios'
import { useDocumentStore } from '@/stores/document'
import ApplicationDetailModal from '@/components/ApplicationDetailModal.vue'
import { formatDateTime, formatDecision } from '@/utils/formatters'
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

function getDecisionSeverity(dec: string): 'success' | 'warn' | 'danger' | 'secondary' {
  switch (dec) {
    case 'approved':
      return 'success'
    case 'revision_required':
      return 'warn'
    case 'rejected':
      return 'danger'
    default:
      return 'secondary'
  }
}

onMounted(() => {
  fetchHistory()
})
</script>
